<!-- Header -->
<div id="header">
  <img id="prestashop_logo" src="theme/img/prestashop_logo.svg" width="180" height="auto" alt="PrestaShop" loading="lazy" />

  <ul id="headerLinks">
    <?php if (is_array($this->getConfig('header.links'))): ?>
      <?php foreach($this->getConfig('header.links') as $link => $label): ?>
        <li>
          <a href="<?php echo $link ?>" target="_blank" rel="noopener noreferrer">
            <?php echo $label; ?>
          </a>
        </li>
      <?php endforeach ?>
    <?php endif; ?>
  </ul>
</div>
