<?php
    if (!defined("IN_ESOTALK")) exit;
    $form = $data["OpauthSettingsForm"];
?>

<?php echo $form->open(); ?>

<script>
    $(document).ready(function() {
        $(":checkbox:not(.static)").each(function() {
            toggleFields(this);
        })
        
        $(":checkbox:not(.static)").click(function() {
            toggleFields(this);
        })
        
        $("label").click(function() {
            var checkbox = $(this).siblings(":checkbox");
            if(checkbox.is(":checked")) {
                checkbox.removeAttr("checked");
            }
            else {
                checkbox.attr("checked", "checked");
            }
            checkbox.triggerHandler("click");
            $(this).parent().siblings("div:not(.disabled)").find(":input:first").focus();
        })
    })
    
    function toggleFields(checkbox) {
        if( $(checkbox).is(":checked") ) {
            $(checkbox).parent().siblings("div").removeClass('disabled').find(":input").removeAttr("disabled");
        }
        else {
            $(checkbox).parent().siblings("div").addClass('disabled').find(":input").attr("disabled", 'disabled').removeAttr('value');
        }
    }
</script>

<div class='section opauth-settings clearfix'>

    <div class="row clearfix">
        <div class="status">
            <label><?php print T('Security salt'); ?></label>
        </div>
        <div>
                <ul class='form'>
                    <li>
                        <?php echo $form->input("security_salt", "text"); ?>
                        <div class="help"><?php print T('Strongly recommend to set your own value!'); ?></div>
                    </li>
                </ul>
        </div>
    </div>
    
    <div class="row clearfix">
        <div class="status">
            <?php print $form->checkbox("strategy[tw]"); ?>
            <label>Twitter</label>
        </div>
        <div>
            <ul class='form'>
                <li>
                    <label>Twitter Key</label>
                    <?php echo $form->input("twitter_key", "text"); ?>
                </li>

                <li>
                    <label>Twitter Secret</label>
                    <?php echo $form->input("twitter_secret", "text"); ?>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row clearfix">
        <div class="status">
            <?php print $form->checkbox("strategy[fb]"); ?>
            <label>Facebook</label>
        </div>
        <div>
            <ul class='form'>
                <li>
                    <label>Facebook App ID</label>
                    <?php echo $form->input("facebook_key", "text"); ?>
                </li>

                <li>
                    <label>Facebook App Secret</label>
                    <?php echo $form->input("facebook_secret", "text"); ?>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row clearfix">
        <div class="status">
            <?php print $form->checkbox("strategy[gg]"); ?>
            <label>Google</label>
        </div>
        <div>
            <ul class='form'>
                <li>
                    <label>Google Client ID</label>
                    <?php echo $form->input("google_key", "text"); ?>
                </li>

                <li>
                    <label>Google Client Secret</label>
                    <?php echo $form->input("google_secret", "text"); ?>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row clearfix">
        <div class="status">
            <?php print $form->checkbox("change_name", array("class" => "static")); ?>
            <label><?php print T('Change nickname'); ?></label>
        </div>
        <div>
                <?php print T('Allows users to change their nicknames.'); ?> <br/>
                <?php print T('This option adds an item to user menu, called "change login".'); ?>
        </div>
    </div>

</div>

<div class='buttons'>
    <?php echo $form->saveButton(); ?>
</div>

<?php echo $form->close(); ?>