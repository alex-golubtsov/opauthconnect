Opauthconnect
=============

Plugin for esoTalk for signing in via social networks.

Steps to install:  
1. Add plugin folder to plugins directory (be sure plugin folder name is 'opauthconnect')  
2. Enable it in admin panel  
3. Check needed social networks  
4. Add following code where you want to display login buttons  
<?php $this->trigger("RenderOpauth"); ?>  
  
To customize buttons check opauthconnect/views/buttons.php file
