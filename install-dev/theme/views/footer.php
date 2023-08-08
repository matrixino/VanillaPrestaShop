<div id="footer">
    <ul>
        <?php if (is_array($this->getConfig('footer.links'))): ?>
            <?php foreach($this->getConfig('footer.links') as $link => $label): ?>
                <li>
                    <a href="<?php echo $link ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo $label; ?>
                    </a>
                    |
                </li>
            <?php endforeach ?>
        <?php endif; ?>

        <li>&copy; 2007-<?php echo date('Y'); ?></li>
    </ul>
    <!-- TODO: La traduction actuel reprend les traductions de PS il faut voir si on peut connecter notre Crowdin et gérer nos propres traductions de manière simple. -->
    <!-- La variable footer.lang se trouve actuellement dans le fichier config.php à la racine du dossier theme de l'install. -->
    <?php
    switch ($this->getConfig('footer.lang')) {
        case 'fr':
            echo "<p>Besoin d’aide ? Nous sommes là pour vous aider. Visitez le <a href='https://help-center.prestashop.com/fr' target='_blank' rel='noopener noreferrer'>Centre d’aide</a> ou <a href='https://help-center.prestashop.com/fr/contact' target='_blank' rel='noopener noreferrer'>contactez-nous</a>.</p>";
            break;
        case 'es':
            echo "<p>¿Tienes preguntas? Estamos aquí para ayudar. Visita el <a href='https://help-center.prestashop.com/es' target='_blank' rel='noopener noreferrer'>Centro de ayuda</a> o <a href='https://help-center.prestashop.com/es/contact' target='_blank' rel='noopener noreferrer'>contáctanos</a>.</p>";
            break;
        case 'it':
            echo "<p>Hai domande? Siamo qui per aiutarti. Visita il <a href='https://help-center.prestashop.com/it' target='_blank' rel='noopener noreferrer'>centro assistenza</a> oppure <a href='https://help-center.prestashop.com/it/contact' target='_blank' rel='noopener noreferrer'>contattaci</a>.</p>";
            break;
        default:
            echo "<p>Any questions? We’re here to help. Visit the <a href='https://help-center.prestashop.com/' target='_blank' rel='noopener noreferrer'>Help Center</a> or <a href='https://help-center.prestashop.com/en/contact' target='_blank' rel='noopener noreferrer'>contact us</a>.</p>";
    }
    ?>
</div>
