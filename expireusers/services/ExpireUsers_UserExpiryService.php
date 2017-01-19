<?php

namespace Craft;

class ExpireUsers_UserExpiryService extends BaseApplicationComponent {

    public function getUserExpiryDate($userId) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if ($record) {
            $model = ExpireUsers_UserExpiryModel::populateModel($record);
            return $model->expiryDate;
        }
        return null;
    }

    public function setUserExpiryDate($userId, $expiryDate) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if (!$record) {
            $record = new ExpireUsers_UserExpiryRecord();
            $record->userId = $userId;
        }
        $record->expiryDate = $expiryDate;
        return $record->save();
    }

    public function clearUserExpiryDate($userId) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if ($record) {
            $record->delete();
        }
    }

    public function checkIfExpired($userId) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if (!$record) {
            // no record therefore no expiry date specified
            return null;
        } else {
            $now = new DateTime("now");
            if ($now > $record->expiryDate) {
                $this->suspendUserById($userId);
                return true;
            }
            return false;
        }
    }

    public function shouldBeExpired($userId) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if (!$record) {
            // no record therefore no expiry date specified
            return false;
        } else {
            $now = new DateTime("now");
            if ($now > $record->expiryDate) {
                return true;
            }
            return false;
        }
    }

    private function suspendUserById($userId) {
        $user = craft()->users->getUserById($userId);
        if ($user && !$user->suspended) {
            return craft()->users->suspendUser($user);
        }
        return false;
    }

    /*
     * MAY IMPLEMENT AT LATER DATE
      private function unsuspendUserById($userId) {
      $user = craft()->users->getUserById($userId);
      if ($user && $user->suspended) {
      return craft()->users->unsuspendUser($user);
      }
      return false;
      }
     */
}
