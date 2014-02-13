<div class="meta">

<!-- Bénéficiare-->
<?php $beneficiaire = get_post_meta(get_the_ID(), 'wpcf-beneficiaire', true) ?>
<?php if(!empty($beneficiaire)): ?><span>Bénéficiaire : <?php echo $beneficiaire; ?> </span><?php endif; ?>

<!-- Image-->
<?php $image = get_post_meta(get_the_ID(), 'wpcf-mon-image', true) ?>
<?php if(!empty($image)): ?><span> <?php echo $image; ?> </span><?php endif; ?>

<!-- Musiciens-->
<?php $musiciens = get_post_meta(get_the_ID(), 'wpcf-musiciens', true) ?>
<?php if(!empty($musiciens)): ?><span>Musiciens : <?php echo $musiciens; ?> </span><?php endif; ?>

<!-- Recette-->
<?php $recette = get_post_meta(get_the_ID(), 'wpcf-recette', true) ?>
<?php if(!empty($recette)): ?><span>Recette : <?php echo $recette; ?> </span><?php endif; ?>

<!-- Lieu-->
<?php $recette = get_post_meta(get_the_ID(), 'wpcf-lieu', true) ?>
<?php if(!empty($lieu)): ?><span>lieu : <?php echo $lieu; ?> </span><?php endif; ?>

</div>