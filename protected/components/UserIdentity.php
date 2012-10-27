<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {

    /**
     * Authenticates a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */
    private $_id;

    public function authenticate() {
        $user = Users::model()->findByAttributes(array('email_usr' => $this->username));

        if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } elseif ($user->enabled_usr == 0) {
            $this->errorCode = 255; //  the user is disabled
        } else {
            if ($user->password_usr !== $user->hashPassword($this->password)) {
                $this->errorCode = self::ERROR_PASSWORD_INVALID;
            } else {
                $this->_id = $user->id_usr;

                //  we set the current logged in user information
                $tmpArray = array();
                $sql = "SELECT r.acronym_rts FROM rights_rts r 
                    LEFT JOIN userrights_uts u ON u.id_rts = r.id_rts 
                    WHERE u.id_usr = {$user->id_usr} AND r.enabled_rts = 1";
                $rows = Yii::app()->db->createCommand($sql)->queryAll();
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        if (isset($row['acronym_rts']) && $row['acronym_rts'] != '')
                            $tmpArray[] = $row['acronym_rts'];
                    }
                }
                $this->setState('userLoggedInInfo', $tmpArray);
                $this->setState('id_usr', $user->id_usr);
                $this->setState('firstname_usr', $user->firstname_usr);
                $this->setState('lastname_usr', $user->lastname_usr);
                $this->setState('email_usr', $user->email_usr);
                $this->setState('rights', $tmpArray);

                $this->errorCode = self::ERROR_NONE;
            }
        }

        return !$this->errorCode;
    }

    public function getId() {
        return $this->_id;
    }

    public static function check($rights) {
        if ($rights == '*')
            return true;

        if ($rights == '@' && !Yii::app()->user->isGuest)
            return true;

        if (!Yii::app()->user->isGuest) {
            $rights = explode(',', $rights);

            $rightsFounded = 0;

            foreach ($rights as $right) {
                $right = trim($right);

                if (!isset(Yii::app()->user->rights) || !is_array(Yii::app()->user->rights))
                    continue;

                $userRights = Yii::app()->user->rights;

                //  we check if the current right, from the controller -> action requested rights, is in the user's rights list
                if (!isset($userRights) || !is_array($userRights))
                    continue;

                if (in_array($right, $userRights))
                    $rightsFounded++;
            }

            //  we check if the users rights includes the controller -> action requested rights
            if ($rightsFounded == count($rights))
                return true;
            else
                return false;
        } else
            return false;
    }

}