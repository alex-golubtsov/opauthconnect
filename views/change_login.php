<div id='settings-password' class='area'>
        <?php echo $data["form"]->open(); ?>
                <ul class='form'>
                        <li>
                            <label><?php echo T("Your new username"); ?></label>
                            <?php echo $data["form"]->input("username", "text"); ?>
                        </li>
                        <li class='sep'></li>
                        <li><?php echo $data["form"]->saveButton(); ?></li>
                </ul>
        <?php echo $data["form"]->close(); ?>
</div>