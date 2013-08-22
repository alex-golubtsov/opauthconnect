<div id="social-container">
    <?php if(isset($data['twitter'])): ?>
<!--        <a href = "<?php print $data['twitter']['url']; ?>" class="twitter">
            <span class="active-bg tw"></span>
            <img src="<?php print $data['twitter']['icon']; ?>"/>
        </a>-->
        <a href = "<?php print $data['twitter']['url']; ?>" class="twitter button toggle">
            <i class="icon"></i> Log in using Twitter
        </a>
    <?php endif; ?>

    <?php if(isset($data['facebook'])): ?>
<!--        <a href = "<?php print $data['facebook']['url']; ?>" class="facebook">
            <span class="active-bg fb"></span>
            <img src="<?php print $data['facebook']['icon']; ?>"/>
        </a>-->
        <a href = "<?php print $data['facebook']['url']; ?>" class="facebook button toggle">
            <i class="icon"></i> Log in using Facebook
        </a>
    <?php endif; ?>

    <?php if(isset($data['google'])): ?>
<!--        <a href = "<?php print $data['google']['url']; ?>" class="google">
            <span class="active-bg gg"></span>
            <img src="<?php print $data['google']['icon']; ?>"/>
        </a>-->
        <a href = "<?php print $data['google']['url']; ?>" class="google button toggle">
            <i class="icon"></i> Log in using Google
        </a>
    <?php endif; ?>
</div>
    
<div id="remember-container">
    <a href="#" class="button toggle <?php if($data["remember"]): ?>button-pressed<?php endif; ?>">
        <i class="icon-check<?php if(!$data["remember"]): ?>-empty<?php endif; ?>"></i> Keep me logged in
    </a>
</div>
    
<script>
    $(document).ready(function() {
        $("div#remember-container a").click(function() {
            var _this = $(this);
            var set = _this.children("i").hasClass("icon-check-empty") ? 1 : 0;
            $.ajax({
                url: ET.webPath + "/user/setremember",
                data: {remember: set},
                type: "POST",
                success: function() {
                    _this.toggleClass("button-pressed")
                         .children("i").toggleClass("icon-check-empty icon-check");
                }
            })
        })
    })
</script>