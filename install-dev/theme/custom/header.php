<!-- Header -->
<div id="header">
    <img id="PrestaShopLogo" src="theme/custom/img/logo.svg">PrestaShop</img>

    <ul id="headerLinks">
        <?php if (is_array($this->getConfig('header.links'))) { ?>
            <?php foreach ($this->getConfig('header.links') as $link => $label) { ?>
                <li>
                    <a href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo $label; ?>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
</div>
