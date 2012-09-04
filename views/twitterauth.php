<?php if (!defined("IN_ESOTALK")) exit; ?>

<div class='sheet' id='messageSheet'>
        <div class='sheetContent'>
                <h3><?php print T('Twitter Authentication'); ?></h3>
                <div class='section help'><?php print T("In accordance with the fact that Twitter does not provide user's e-mail, enter your e-mail here."); ?></div>
                
                <div class="section section-form">
                    <?php print $data['form']->open(); ?>
                    <label><?php print T("E-mail"); ?>:</label>
                    <?php print $data['form']->input("email", "text"); ?>
                </div>
                
                <div class='buttons'>
                        <?php print $data['form']->button("save", T("Continue"), array('class' => 'big submit')); ?>
                </div>
                <?php print $data['form']->close(); ?>
        </div>
</div>