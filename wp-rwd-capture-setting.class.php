<?php

/* wp-rwd-capture setting page */
class CaptureSettingsPage
{
    /** configuration options */
    private $options;

    /**
     * init
     */
    public function __construct()
    {
        // メニューを追加します。
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        // ページの初期化を行います。
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * add page to the menu
     */
    public function add_plugin_page()
    {
        // add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        //   $page_title: 設定ページの<title>部分
        //   $menu_title: メニュー名
        //   $capability: 権限 ( 'manage_options' や 'administrator' など)
        //   $menu_slug : メニューのslug
        //   $function  : 設定ページの出力を行う関数
        //   $icon_url  : メニューに表示するアイコン
        //   $position  : メニューの位置 ( 1 や 99 など )
        add_menu_page( 'Capture Settings', 'Capture Settings', 'manage_options', 'capture_setting', array( $this, 'create_admin_page' ) );

        // 設定のサブメニューとしてメニューを追加する場合は下記のような形にします。
        // add_options_page( 'テスト設定', 'テスト設定', 'manage_options', 'test_setting', array( $this, 'create_admin_page' ) );
    }

    /**
     * 設定ページの初期化を行います。
     */
    public function page_init()
    {
        // 設定を登録します(入力値チェック用)。
        // register_setting( $option_group, $option_name, $sanitize_callback )
        //   $option_group      : 設定のグループ名
        //   $option_name       : 設定項目名(DBに保存する名前)
        //   $sanitize_callback : 入力値調整をする際に呼ばれる関数
        register_setting( 'capture_setting', 'capture_setting', array( $this, 'sanitize' ) );

        // 入力項目のセクションを追加します。
        // add_settings_section( $id, $title, $callback, $page )
        //   $id       : セクションのID
        //   $title    : セクション名
        //   $callback : セクションの説明などを出力するための関数
        //   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
        add_settings_section( 'capture_setting_section_id', '', '', 'capture_setting' );

        // 入力項目のセクションに項目を1つ追加します(今回は「メッセージ」というテキスト項目)。
        // add_settings_field( $id, $title, $callback, $page, $section, $args )
        //   $id       : 入力項目のID
        //   $title    : 入力項目名
        //   $callback : 入力項目のHTMLを出力する関数
        //   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
        //   $section  : セクションのID (add_settings_section()の$idと同じものにする)
        //   $args     : $callbackの追加引数 (必要な場合のみ指定)
        add_settings_field( 'apikey', 'API key', array( $this, 'apikey_callback' ), 'capture_setting', 'capture_setting_section_id' );
        add_settings_field( 'endpoint', 'API Endpoint', array( $this, 'endpoint_callback' ), 'capture_setting', 'capture_setting_section_id' );
        add_settings_field( 'template', 'UA Templates Endpoint', array( $this, 'template_callback' ), 'capture_setting', 'capture_setting_section_id' );

    }

    /**
     * Output html
     */
    public function create_admin_page()
    {
        // 設定値を取得します。
        $this->options = get_option( 'capture_setting' );
        ?>
        <div class="wrap">
            <h2>WP-Capture Configurations</h2>
	    <p>
		You need to create an account for <a href="https://screenshot-web.com/">screenshot-web.com</a> first.<br/>
		After you create an accout, go to settings page and copy your APIKEY.
	    </p>
            <?php
            // ※ add_menu_page()の場合親ファイルがoptions-general.phpではない
            global $parent_file;
            if ( $parent_file != 'options-general.php' ) {
                require(ABSPATH . 'wp-admin/options-head.php');
            }
            ?>
            <form method="post" action="options.php">
            <?php
                // 隠しフィールドなどを出力します(register_setting()の$option_groupと同じものを指定)。
                settings_fields( 'capture_setting' );
                // 入力項目を出力します(設定ページのslugを指定)。
                do_settings_sections( 'capture_setting' );
                // 送信ボタンを出力します。
                submit_button();
            ?>
            </form>
	    <h3>Template List</h3>
	    <p>shortcode example</p>
	    <blockquote style="background-color:#f8f8f8; padding:20px">
		#Default(Google Chrome)<br> [ssweb]http://example.com[/ssweb]<br><br>
	    	#Full Page<br> [ssweb height=0]http://example.com[/ssweb]<br><br>
	    	#Set iPhone6 as UA<br> [ssweb <font color="red">template_id=3</font>]http://example.com[/ssweb]<br><br>
	    	#Specify selector<br> [ssweb selector="#wsod_worldMarkets"]http://money.cnn.com/data/world_markets/americas/[/ssweb]<br><br>
	    </blockquote>
	    <p><?php include_once("templates.getua.inc.html");?></p>
	    <p class="form-control" id="templates" name="templates"></p>
        </div>
        <?php
    }

    /**
     * apikeyのHTMLを出力します。
     */
    public function apikey_callback()
    {
        // 値を取得
        $apikey = isset( $this->options['apikey'] ) ? $this->options['apikey'] : '';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="apikey" size="50" name="capture_setting[apikey]" value="<?php esc_attr_e( $apikey ) ?>" /><?php
    }

    /**
     * endpointのHTMLを出力します。
     */
    public function endpoint_callback()
    {
        // 値を取得
        $ep = isset( $this->options['endpoint'] ) ? $this->options['endpoint'] : 'https://screenshot-web.com/api/capture/';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="endpoint" size="50" name="capture_setting[endpoint]" value="<?php esc_attr_e( $ep ) ?>" /><?php
    }

    /**
     * UA template list のHTMLを出力します。
     */
    public function template_callback()
    {
        // 値を取得
        $tpl = isset( $this->options['template'] ) ? $this->options['template'] : 'https://screenshot-web.com/templates/';
        // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
        ?><input type="text" id="template" size="50" name="capture_setting[template]" value="<?php esc_attr_e( $tpl ) ?>" /><?php
    }
 
    /**
     * 送信された入力値の調整を行います。
     *
     * @param array $input 設定値
     */
    public function sanitize( $input )
    {
        // DBの設定値を取得します。
        $this->options = get_option( 'capture_setting' );

        $new_input = array();

        // APIKEYがある場合値を調整
        if( isset( $input['apikey'] ) && trim( $input['apikey'] ) !== '' ) {
            $new_input['apikey'] = sanitize_text_field( $input['apikey'] );
        }
        else {
            add_settings_error( 'capture_setting', 'apikey', 'Please input API KEY' );

            // 値をDBの設定値に戻します。
            $new_input['apikey'] = isset( $this->options['apikey'] ) ? $this->options['apikey'] : '';
        }

        // ENDPOINTがある場合値を調整
        if( isset( $input['endpoint'] ) && trim( $input['endpoint'] ) !== '' ) {
            $new_input['endpoint'] = sanitize_text_field( $input['endpoint'] );
        }
        else {
            //add_settings_error( 'capture_setting', 'apikey', 'API End Point: default endpoint is https://screenshot-web.com/api/capture/' );

            // 値をDBの設定値に戻します。
            $new_input['endpoint'] = isset( $this->options['endpoint'] ) ? $this->options['endpoint'] : 'https://screenshot-web.com/api/capture/';
        }
        // ENDPOINTがある場合値を調整
        if( isset( $input['template'] ) && trim( $input['template'] ) !== '' ) {
            $new_input['template'] = sanitize_text_field( $input['template'] );
        }
        else {

            // 値をDBの設定値に戻します。
            $new_input['template'] = isset( $this->options['template'] ) ? $this->options['template'] : 'https://screenshot-web.com/templates/';
        }



        return $new_input;
    }

}


