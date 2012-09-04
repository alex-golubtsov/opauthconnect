<?php if(isset($data['twitter_url'])): ?>
    <a href = "<?php print $data['twitter_url']; ?>" class="twitter">Twitter</a>
<?php endif; ?>

<?php if(isset($data['facebook_url'])): ?>
    <a href = "<?php print $data['facebook_url']; ?>" class="facebook">Facebook</a>
<?php endif; ?>

<?php if(isset($data['google_url'])): ?>
    <a href = "<?php print $data['google_url']; ?>" class="google">Google</a>
<?php endif; ?>