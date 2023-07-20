<?php
    function get_user_ip()
    {
      /* handle CloudFlare IP addresses */
      return (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR']);
    }
    
    function get_cookie_user($user_id)
    {
        $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt_result = $stmt->get_result();
        $stmt->close();
        $row = $stmt_result->fetch_array(MYSQLI_ASSOC);
        brutal_force_prevention($row);
        return $row;
    }

     /**
     * _set_authentication_cookies
     * 
     * @param integer $user_id
     * @param boolean $remember
     * @param string $path
     * @return void
     */
    function _set_authentication_cookies($user_id, $remember = false, $path = '/')
    {
      global $db, $system, $date;
      /* generate new token */
      $session_token = get_hash_token();
      /* secured cookies */
      $secured = (get_system_protocol() == "https") ? true : false;
      $browser = get_user_browser();
      $os = get_user_os();
      $ip = get_user_ip();

      /* insert user token */
      $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $stmt = $connection->prepare("INSERT INTO users_sessions (session_token, session_date, user_id, user_browser, user_os, user_ip) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssisss", $session_token, $date, $user_id, $browser, $os, $ip);
      $stmt->execute();
    //  $stmt->close();
    }
    
    /**
     * get_hash_token
     * 
     * @return string
     */
    function get_hash_token()
    {
      return md5(get_hash_number());
    }

    /**
     * get_system_protocol
     * 
     * @return string
     */
    function get_system_protocol()
    {
      $is_secure = false;
      if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
        $is_secure = true;
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        $is_secure = true;
      }
      return $is_secure ? 'https' : 'http';
    }

    /**
     * get_hash_number
     * 
     * @return string
     */
    function get_hash_number()
    {
      return time() * rand(1, 99999);
    }

    /**
     * get_browser
     * 
     * @return string
     */
    function get_user_browser()
    {
      $browser = "Unknown Browser";
      $browser_array = array(
        '/msie/i'       =>  'Internet Explorer',
        '/firefox/i'    =>  'Firefox',
        '/safari/i'     =>  'Safari',
        '/chrome/i'     =>  'Chrome',
        '/edge/i'       =>  'Edge',
        '/opera/i'      =>  'Opera',
        '/netscape/i'   =>  'Netscape',
        '/maxthon/i'    =>  'Maxthon',
        '/konqueror/i'  =>  'Konqueror',
        '/mobile/i'     =>  'Handheld Browser'
      );
      foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
          $browser = $value;
        }
      }
      return $browser;
    }
    
    /**
     * get_os
     * 
     * @return string
     */
    function get_user_os()
    {
      $os_platform = "Unknown OS Platform";
      $os_array = array(
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
      );
      foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
          $os_platform = $value;
        }
      }
      return $os_platform;
    }

    function brutal_force_prevention($user)
    {
        /* check brute-force attack detection */
        $brute_force_lockout_time = 10 * 60; /* convert to minutes */
        if (($user['user_failed_login_ip'] == get_user_ip()) && ($user['user_failed_login_count'] >= 5) && (time() - strtotime($user['user_first_failed_login']) <  $brute_force_lockout_time)) {
            return ["error" => "Your account currently locked out, Please try again later!"];
        }
    }
        
        
    /* ------------------------------- */
    /* Images */
    /* ------------------------------- */
    
    /**
     * get_picture
     * 
     * @param string $picture
     * @param string $type
     * @return string
     */
    function get_picture($picture, $type)
    {
      global $system;
      if ($picture == "") {
        switch ($type) {
             case '1':
                $picture = '/comunidade/content/themes/default/images/blank_profile_male.svg';
                break;
              case '2':
                $picture = '/comunidade/content/themes/default/images/blank_profile_female.svg';
                break;
             default:
                $picture = '/comunidade/content/themes/default/images/blank_profile.svg';
                break;
            }
      } else {
        $picture = '/comunidade/content/uploads/' . $picture;
      }
      return $picture;
    }

    