<?php
if (!defined("IN_ESOTALK")) exit;

ET::$pluginInfo["opauthconnect"] = array(
	"name" => "Opauth.connect",
	"description" => "Sign in via social networks",
	"version" => "1.0.0.2",
	"author" => "Alex G.",
	"authorEmail" => "alex.8fmi@gmail.com",
        "authorURL" => "http://mikrobill.com",
	"license" => "GPLv2"
);

require_once "lib".DIRECTORY_SEPARATOR."Opauth.php";

class ETPlugin_Opauthconnect extends ETPlugin {
    
    private static $CONFIG_PREFIX = "plugin.opauthconnect.";
    private static $DEFAULT_SECURITY_SALT = "01wLr9OE0TZkIhkUsIJm";
    private $config = array();
    
    public function __construct($rootDirectory) {
        parent::__construct($rootDirectory);
        $this->config['security_salt'] = $this->C("security_salt") ? $this->C("security_salt") : self::$DEFAULT_SECURITY_SALT;
        $this->config['path'] = URL('user/auth/');
        $this->config['callback_url'] = URL('user/scallback/');
        
        if( $this->C("twitter_enable") ) {
            $this->config['Strategy']['Twitter'] = array(
                'key' => $this->C("twitter_key"),
                'secret' => $this->C("twitter_secret")
            );
        }
        
        if( $this->C("facebook_enable") ) {
            $this->config['Strategy']['Facebook'] = array(
                'app_id' => $this->C("facebook_key"),
                'app_secret' => $this->C("facebook_secret"),
                'scope' => 'email'
            );
        }
        
        if( $this->C("google_enable") ) {
            $this->config['Strategy']['Google'] = array(
                'client_id' => $this->C("google_key"),
                'client_secret' => $this->C("google_secret")
            );
        }
    }
    
    private function C($name) {
        return C(self::$CONFIG_PREFIX.$name);
    }
    
    public function userController_setRemember() {
        ET::$session->store("remember", (int) R("remember", 0));
    }
    
    public function handler_init($sender) {
        if(ET::$session->get("remember") === null) ET::$session->store("remember", 1);
        if( isset($sender->menus['user']->items['join']) ) {
            unset($sender->menus['user']->items['join']);
        }
        $sender->addCSSFile($this->getResource("opauth.css"));
        ET::define("message.logInToReply", "<a href='%1\$s' class='link-login'>Log In</a> or <a href='%1\$s' class='link-login'>Sign Up</a> to reply!", true);
    }
    
    public function handler_RenderOpauth($sender) {
        $data = array();
        if( $this->C("twitter_enable") ) {
            $data['twitter'] = array(
                'url' => URL('user/auth/twitter'),
                'icon' => URL($this->getResource("images/tw.png"))
            );
        }
        if( $this->C("facebook_enable") ) {
            $data['facebook'] = array(
                'url' => URL('user/auth/facebook'),
                'icon' => URL($this->getResource("images/fb.png"))
            );
        }
        if( $this->C("google_enable") ) {
            $data['google'] = array(
                'url' => URL('user/auth/google'),
                'icon' => URL($this->getResource("images/gg.png"))
            );
        }
        $data["remember"] = ET::$session->get("remember", 0);
        
        print $sender->getViewContents('buttons', $data);
    }
    
    public function settings($sender) {
        $sender->addCSSFile($this->getResource("settings.css"));
        $form = ETFactory::make("form");
        $form->action = URL("admin/plugins");
        $form->setValue( "strategy[tw]",    $this->C("twitter_enable") );
        $form->setValue( "twitter_key",     $this->C("twitter_key") );
        $form->setValue( "twitter_secret",  $this->C("twitter_secret") );
        $form->setValue( "strategy[fb]",    $this->C("facebook_enable") );
        $form->setValue( "facebook_key",    $this->C("facebook_key") );
        $form->setValue( "facebook_secret", $this->C("facebook_secret") );
        $form->setValue( "strategy[gg]",    $this->C("google_enable") );
        $form->setValue( "google_key",      $this->C("google_key") );
        $form->setValue( "google_secret",   $this->C("google_secret") );
        $form->setValue( "change_name",     $this->C("change_name") );
        $form->setValue( "security_salt",   $this->C("security_salt") ? $this->C("security_salt") : self::$DEFAULT_SECURITY_SALT);

        if ($form->validPostBack("save")) {
            $config = array();
            $config[self::$CONFIG_PREFIX."twitter_enable"] = $form->getValue("strategy[tw]");
            $config[self::$CONFIG_PREFIX."twitter_key"] = $form->getValue("twitter_key");
            $config[self::$CONFIG_PREFIX."twitter_secret"] = $form->getValue("twitter_secret");
            $config[self::$CONFIG_PREFIX."facebook_enable"] = $form->getValue("strategy[fb]");
            $config[self::$CONFIG_PREFIX."facebook_key"] = $form->getValue("facebook_key");
            $config[self::$CONFIG_PREFIX."facebook_secret"] = $form->getValue("facebook_secret");
            $config[self::$CONFIG_PREFIX."google_enable"] = $form->getValue("strategy[gg]");
            $config[self::$CONFIG_PREFIX."google_key"] = $form->getValue("google_key");
            $config[self::$CONFIG_PREFIX."google_secret"] = $form->getValue("google_secret");
            $config[self::$CONFIG_PREFIX."change_name"] = $form->getValue("change_name");
            $config[self::$CONFIG_PREFIX."security_salt"] = $form->getValue("security_salt");

            if (!$form->errorCount()) {
                ET::writeConfig($config);
                $sender->message(T("message.changesSaved"), "success");
                $sender->redirect(URL("admin/plugins"));
            }
        }

        $sender->data("OpauthSettingsForm", $form);
        return $this->getView('settings');
    }

//     public function settingsController_password($sender) {}
    
    public function userController_auth($sender, $sn) {
        if (ET::$session->user) {
            $sender->message( T('You are already logged in.') );
            redirect(URL(""));
        }
        
        if( in_array($sn, array('facebook', 'google', 'twitter')) ) {
            $opauth = new Opauth($this->config);
            redirect($this->config['path'].$sn);
        }
    }
    
    private function twitterauthhandler($sender, $response) {
        $sql = ET::SQL()->select(array('email', 'username', 'memberId', 'resetPassword', 'TWconfirmed'))
                        ->from("member")
                        ->where('TWid', $response['auth']['uid'])
//                        ->where('fromSN', 1)
                        ->exec()
                        ->firstRow();
        if(!empty($sql) && !$sql['TWconfirmed']) {
            $params = array();
            $params['confirmation_url'] = URL("user/twitterconfirm/".$sql['memberId'].$sql['resetPassword'], true);
            sendEmail($sql['email'],
                    "First signing in via Twitter",
                    $sender->getViewContents('twitter_confirmation', $params));

            $sender->message("Confirmation was sent to your e-mail address(".$sql['email']."). Please confirm before using twitter.", "success");
            redirect( URL("") );
        }
        elseif(!empty($sql) && $sql['TWconfirmed']) {
            return $sql['email'];
        }
        else {
            ET::$session->store("twitter_response", $response);
            redirect(URL("user/twitterauth"));
        }
    }
    
    public function userController_twitterauth($sender) {
        $form = ETFactory::make("form");
        $form->action = URL("user/twitterauth");

        if ( $form->validPostBack("save") ) {
            if(ET::memberModel()->validateEmail($form->getValue('email'), false) !== NULL) {
                $form->error("email", T("Incorrect e-mail."));
            }
            if(!$form->errorCount()) {
                $response = ET::$session->get("twitter_response");
                $data = array(
                    "username" => $this->generate_nickname(ET::memberModel(), $response['auth']['info']['name'], $form->getValue('email')),
                    "email" => $form->getValue('email'),
                    "password" => generateRandomString(32),
                    "account" => ACCOUNT_MEMBER,
                    "resetPassword" => md5(uniqid(rand())),
                    "confirmedEmail" => 1,
                    "fromSN" => 1,
                    "TWid" => $response['auth']['uid']
                );
                $memberID = ET::memberModel()->create($data);
                if(!empty($response['auth']['info']['image'])) {
                    $avatar = ET::uploader()->saveAsImage($response['auth']['info']['image'], PATH_UPLOADS."/avatars/".$memberID, C("esoTalk.avatars.width"), C("esoTalk.avatars.height"), "crop");
                    ET::memberModel()->updateById($memberID, array("avatarFormat" => pathinfo($avatar, PATHINFO_EXTENSION)));
                }
                ET::$session->remove("twitter_response");
                $opauth = new Opauth($this->config);
                redirect($this->config['path']."twitter");
            }
        }

        $sender->title = T("Twitter Authentication");
        $sender->data('form', $form);
        $sender->render('twitterauth');
    }
    
    public function userController_twitterconfirm($sender, $hash) {
        $memberId = (int)substr($hash, 0, strlen($hash) - 32);
	$hash = substr($hash, -32);
        
        $result = ET::SQL()->select("1")
                           ->from("member")
                           ->where("memberId", $memberId)
                           ->where("resetPassword", $hash)
                           ->where("confirmedEmail", false)
                           ->exec();
        
        if ($result->numRows()) {
            ET::memberModel()->updateById($memberId, array(
			"resetPassword" => null,
			"confirmedEmail" => true,
                        "TWconfirmed" => true
		));

            ET::$session->loginWithMemberId($memberId);
            if( ET::$session->get("remember") ) ET::$session->setRememberCookie($memberId);
        }
        else {
            $sender->message(T("Something went wrong. Can't cofirm your email."), "warning");
        }
        redirect(URL(""));
    }
    
    private function generate_nickname($model, $name, $email) {
        $email = explode("@", $email);
        $email = preg_replace("/[[:punct:]]/", "_", $email[0]);
        $name = str_replace(" ", "_", $name);
        
        if( $model->validateUsername($name) === NULL ) {
            $nick = $name;
        }
        else {
            $nick = $email;
            while( $model->validateUsername($nick) !== NULL ) {
                $nick = $email . "_" . rand(0, 999);
            }
        }
        
        return $nick;
    }
    
    public function handler_settingsController_renderBefore($sender) {
        if( $this->C("change_name") ) {
            $sender->data['panes']->add('login', "<a href='".URL("settings/login")."'>".T("Change login")."</a>");
        }
        
        $user = ET::memberModel()->getById( ET::$session->get("userId") );
//        if( $user['fromSN'] ) {
            unset($sender->data['panes']->items['password']);
//        }
    }
    
    public function settingsController_login($sender) {
        if( !$this->C("change_name") ) {
            $sender->render404();
            return;
        }
        
        $sender->dispatch("profile", array("login"));
        $form = ETFactory::make("form");
        $form->action = URL("settings/login");

        if ($form->validPostBack("save")) {
            $model = ET::memberModel();

            if ($model->validateUsername($form->getValue('username')) == 'nameTaken') {
                $form->error("username", T("This username is already exists"));
            }
            elseif($model->validateUsername($form->getValue('username')) == 'invalidUsername') {
                $form->error("username", T("Username is incorrect"));
            }
            else {
                $update['username'] = $form->getValue('username');
            }

            if (!$form->errorCount() && isset($update)) {
                $model->updateById(ET::$session->userId, $update);
                if ($model->errorCount()) {
                    $form->errors($model->errors());
                }                            
                else {
                    $sender->message(T("message.changesSaved"), "success");
                    $sender->redirect(URL("settings"));
                }
            }
        }

        $sender->data("form", $form);
        $sender->renderProfile($this->getView("change_login"));
    }
    
    public function userController_scallback($sender) {
        if( $response = $this->auth_response_validate($sender) ) {
            if( $response['auth']['provider'] == 'Twitter' ) {
                $email = $this->twitterauthhandler($sender, $response);
                if(!$email) {
                    return;
                }
                $response['auth']['info']['email'] = $email;
                $confirmedEmail = false;
            }
            else {
                $response['auth']['uid'] = 0;
                $confirmedEmail = true;
            }
            
            $model = ET::memberModel();
            $email_response = $model->validateEmail($response['auth']['info']['email']);
            switch($email_response) {
                case NULL:
                        $name = $this->generate_nickname($model, $response['auth']['info']['name'], $response['auth']['info']['email']);
                        $data = array(
                            "username" => $name,
                            "email" => $response['auth']['info']['email'],
                            "password" => generateRandomString(32),
                            "account" => ACCOUNT_MEMBER,
                            "resetPassword" => !$confirmedEmail ? null : md5(uniqid(rand())),
                            "confirmedEmail" => 1,
                            "fromSN" => 1,
                            "TWid" => $response['auth']['uid']
                        );
                        
                        $memberId = $model->create($data);
                        ET::$session->loginWithMemberId($memberId);
                        if( ET::$session->get("remember") ) ET::$session->setRememberCookie($memberId);
                        
                        if(!empty($response['auth']['info']['image'])) {
                            $avatar = ET::uploader()->saveAsImage($response['auth']['info']['image'], PATH_UPLOADS."/avatars/".ET::$session->userId, C("esoTalk.avatars.width"), C("esoTalk.avatars.height"), "crop");
                            ET::memberModel()->updateById(ET::$session->userId, array("avatarFormat" => pathinfo($avatar, PATHINFO_EXTENSION)));
                        }
                        
                        break;

                case 'emailTaken':
                        $sql = ET::SQL()->select('memberId')
                                        ->from("member")
                                        ->where('email', $response['auth']['info']['email'])
//                                        ->where('fromSN', 1)
                                        ->exec()
                                        ->result();
                        ET::$session->loginWithMemberId($sql);
                        if( ET::$session->get("remember") ) ET::$session->setRememberCookie($sql);
                        break;

                default:
                        $sender->message( T('Given e-mail is invalid.') , 'warning');
                        break;
            }
        }
         redirect(URL(""));
    }
    
    private function auth_response_validate($sender) {
        $Opauth = new Opauth( $this->config, false );
        $response = null;

        switch($Opauth->env['callback_transport']) {	
            case 'session':
                    $response = ET::$session->get('opauth');
                    ET::$session->remove('opauth');
                    break;
            case 'post':
                    $response = unserialize(base64_decode( $_POST['opauth'] ));
                    break;
            case 'get':
                    $response = unserialize(base64_decode( $_GET['opauth'] ));
                    break;
            default:
                    $sender->message( T('Unsupported callback_transport.') , 'warning');
                    return false;
        }
        
        if( array_key_exists('error', $response) ) {
            $error = json_decode($response['error']['raw']);
            $sender->message( T('Authentication error').". ".$error->errors[0]->message , 'warning');
            return false;
        }
        else {
                if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
                        $sender->message( T('Invalid auth response: Missing key auth response components.') , 'warning');
                        return false;
                }
                elseif (!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)){
                        $sender->message( T('Invalid auth response: '.$reason) , 'warning');
                        return false;
                }
                else{
                    if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
                            $sender->message( T('Invalid auth response: Missing key auth response components.') , 'warning');
                            return false;
                    }
                    elseif (!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)){
                            $sender->message( T('Invalid auth response: '.$reason) , 'warning');
                            return false;
                    }
                    else {
                        return $response;
                    }
                }
        }
    }
    
    public function setup($oldVersion = "") {
        ET::$database->structure()->table("member")
                                  ->column("fromSN", "tinyint unsigned", false)
                                  ->column("TWid", "int(11) unsigned", false)
                                  ->column("TWconfirmed", "tinyint unsigned", false)
                                  ->exec(false);
        return true;
    }
    
    /**
     * TODO: In newest version of esoTalk remove "plugin.opauthconnect.*" from config.
     * Now there is no way to do this.
     */
    public function uninstall() {
        
    }
}