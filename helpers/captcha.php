<?php

class Captcha{

    public static function gen(){
        $int1 = rand(0,50);
        $int2 = rand(0,50);
        Base::instance()->set('SESSION.captcha',$int1+$int2);
        return ['int1'=> $int1, 'int2'=> $int2];
    }

    public static function mustShow(){
        $db = Base::instance()->get('DB');
        $row = $db->exec('SELECT * from login_attempts where ip = ?',$_SERVER['REMOTE_ADDR']);
        if(empty($row)) return false;
        $last = $row[0]['last_attempt'];
        if($last != null) {
            $last = new DateTime($last);
        } else {
            $last = new DateTime();
        }
        $passed = $last->diff(new DateTime());
        $minutes = $passed->days * 24 * 60;
        $minutes += $passed->h * 60;
        $minutes += $passed->i;
        if($row[0]['attempts'] > 2 && $minutes < 10) return true;
        return false;
    }
}

?>