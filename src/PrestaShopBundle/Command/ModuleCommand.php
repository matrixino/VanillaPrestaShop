<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Command;

use Employee;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Module\AdminModuleDataProvider;
use PrestaShop\PrestaShop\Adapter\Module\Configuration\ModuleSelfConfigurator;
use PrestaShop\PrestaShop\Adapter\Module\ModuleDataProvider;
use PrestaShop\PrestaShop\Core\Context\ContextBuilderPreparer;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Translation\TranslatorInterface;

class ModuleCommand extends Command
{
    private $allowedActions = [
        'install',
        'uninstall',
        'enable',
        'disable',
        'reset',
        'upgrade',
        'configure',
        'delete',
        'list',
    ];

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(
        protected readonly TranslatorInterface $translator,
        protected readonly LegacyContext $context,
        protected readonly ModuleSelfConfigurator $moduleSelfConfigurator,
        protected readonly ModuleManager $moduleManager,
        protected readonly ContextBuilderPreparer $contextBuilderPreparer,
        protected readonly Configuration $configuration,
        protected readonly ModuleDataProvider $moduleDataProvider,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('prestashop:module')
            ->setDescription('Manage your modules via command line')
            ->addArgument('action', InputArgument::REQUIRED, sprintf('Action to execute (Allowed actions: %s).', implode(' / ', $this->allowedActions)))
            ->addArgument('module name', InputArgument::OPTIONAL, 'Module on which the action will be executed (not required for the "list" action)')
            ->addArgument('file path', InputArgument::OPTIONAL, 'YML file path for configuration')
            ->addOption('skip-overrides', null, InputOption::VALUE_NONE, 'Skip installing/uninstalling module overrides')
            ->addOption('all', null, InputOption::VALUE_NONE, 'When used with the "list" action, include uninstalled modules.')
            ->addOption('active', null, InputOption::VALUE_NONE, 'When used with the "list" action, show only installed and enabled modules.')
            ->addOption('disabled', null, InputOption::VALUE_NONE, 'When used with the "list" action, show only installed but disabled modules.')
            ->addOption('not-installed', null, InputOption::VALUE_NONE, 'When used with the "list" action, show only modules present on disk but not installed.');
    }

    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        // We need to have an employee or the module hooks don't work
        // see LegacyHookSubscriber
        if (!$this->context->getContext()->employee) {
            // Even a non existing employee is fine
            $this->context->getContext()->employee = new Employee(42);
        }

        // We must initialize the language context because ModuleRepository depends on it for its cache key
        $this->contextBuilderPreparer->prepareLanguageId($this->configuration->get('PS_LANG_DEFAULT'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->init($input, $output);

        $skipOverrides = (bool) $input->getOption('skip-overrides');

        $moduleName = $input->getArgument('module name');
        $action = $input->getArgument('action');
        $file = $input->getArgument('file path');

        if (!in_array($action, $this->allowedActions)) {
            $this->displayMessage(
                $this->translator->trans(
                    'Unknown module action. It must be one of these values: %actions%',
                    ['%actions%' => implode(' / ', $this->allowedActions)],
                    'Admin.Modules.Notification'
                ),
                'error'
            );

            return 1;
        }

        if ($action === 'list') {
            $filterFlags = [
                'all' => (bool) $input->getOption('all'),
                'active' => (bool) $input->getOption('active'),
                'disabled' => (bool) $input->getOption('disabled'),
                'not-installed' => (bool) $input->getOption('not-installed'),
            ];

            $enabledFilters = array_keys(array_filter($filterFlags));
            if (count($enabledFilters) > 1) {
                $this->displayMessage(
                    sprintf(
                        'The --%s options are mutually exclusive; only one may be passed at a time.',
                        implode(', --', $enabledFilters)
                    ),
                    'error'
                );

                return 1;
            }

            $filter = $enabledFilters[0] ?? 'installed';
            $this->executeListAction($output, $filter);

            return 0;
        }

        if (empty($moduleName)) {
            $this->displayMessage(
                sprintf('A module name is required for the "%s" action.', $action),
                'error'
            );

            return 1;
        }

        if ($skipOverrides) {
            $disableModuleOriginaleValue = $this->configuration->get('PS_DISABLE_MODULE_OVERRIDES');
            $this->configuration->setTemporary('PS_DISABLE_MODULE_OVERRIDES', 1);
        }

        try {
            if ($action === 'configure') {
                $this->executeConfigureModuleAction($moduleName, $file);
            } else {
                $this->executeGenericModuleAction($action, $moduleName);
            }
        } finally {
            if ($skipOverrides) {
                $this->configuration->setTemporary('PS_DISABLE_MODULE_OVERRIDES', $disableModuleOriginaleValue);
            }
        }

        return 0;
    }

    protected function executeListAction(OutputInterface $output, string $filter): void
    {
        $installed = $this->moduleDataProvider->getInstalled();
        $rows = [];

        if ($filter !== 'not-installed') {
            foreach ($installed as $module) {
                $isActive = !empty($module['active']);
                if ($filter === 'active' && !$isActive) {
                    continue;
                }
                if ($filter === 'disabled' && $isActive) {
                    continue;
                }
                $rows[$module['name']] = [
                    $module['name'],
                    (string) $module['version'],
                    $isActive ? 'Enabled' : 'Disabled',
                ];
            }
        }

        if (in_array($filter, ['all', 'not-installed'], true)) {
            $moduleDir = $this->configuration->get('_PS_MODULE_DIR_');
            if (is_string($moduleDir) && is_dir($moduleDir)) {
                $directories = (new Finder())->directories()
                    ->in($moduleDir)
                    ->depth('== 0')
                    ->exclude(['__MACOSX'])
                    ->ignoreVCS(true);
                foreach ($directories as $dir) {
                    $name = $dir->getFilename();
                    if (isset($rows[$name]) || isset($installed[$name])) {
                        continue;
                    }
                    // A directory only counts as a module if it contains a {name}/{name}.php class file.
                    if (!is_file($dir->getPathname() . '/' . $name . '.php')) {
                        continue;
                    }
                    $rows[$name] = [$name, '-', 'Not installed'];
                }
            }
        }

        $rows = array_values($rows);
        usort($rows, static fn (array $a, array $b) => strcasecmp($a[0], $b[0]));

        $table = new Table($output);
        $table->setHeaders(['Name', 'Version', 'Status']);
        $table->setRows($rows);
        $table->render();
    }

    protected function executeConfigureModuleAction($moduleName, $file = null)
    {
        $this->moduleSelfConfigurator->module($moduleName);
        if ($file) {
            $this->moduleSelfConfigurator->file($file);
        }

        // Check if validation passed and exit in case of errors
        $errors = $this->moduleSelfConfigurator->validate();
        if (!empty($errors)) {
            // Display errors as a list
            $errors = array_map(function ($val) { return '- ' . $val; }, $errors);
            // And add a default message at the top
            array_unshift($errors, $this->translator->trans(
                'Validation of configuration details failed:',
                [],
                'Admin.Modules.Notification'
            ));
            $this->displayMessage($errors, 'error');

            return;
        }

        // Actual configuration
        $this->moduleSelfConfigurator->configure();
        $this->displayMessage(
            $this->translator->trans('Configuration successfully applied.', [], 'Admin.Modules.Notification'),
            'info'
        );
    }

    protected function executeGenericModuleAction($action, $moduleName)
    {
        if ($this->moduleManager->{$action}($moduleName)) {
            $this->displayMessage(
                $this->translator->trans(
                    '%action% action on module %module% succeeded.',
                    [
                        '%action%' => ucfirst(AdminModuleDataProvider::ACTIONS_TRANSLATION_LABELS[$action]),
                        '%module%' => $moduleName, ],
                    'Admin.Modules.Notification'
                )
            );

            return;
        }

        $error = $this->moduleManager->getError($moduleName);
        $this->displayMessage(
            $this->translator->trans(
                'Cannot %action% module %module%. %error_details%',
                [
                    '%action%' => str_replace('_', ' ', $action),
                    '%module%' => $moduleName,
                    '%error_details%' => $error, ],
                'Admin.Modules.Notification'
            ),
            'error'
        );
    }

    protected function displayMessage($message, $type = 'info')
    {
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');

        $this->output->writeln(
            $formatter->formatBlock($message, $type, true)
        );
    }
}
