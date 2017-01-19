<?php

namespace Craft;

class ExpireUsers_UserExpiryService extends BaseApplicationComponent {

    /**
     * Get the user's expiry date
     *
     * @param int $userId
     * @return DateTime/Null
     */
    public function getUserExpiryDate($userId) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if ($record) {
            $model = ExpireUsers_UserExpiryModel::populateModel($record);
            return $model->expiryDate;
        }
        return null;
    }

    /**
     * Sets the user's expiry date
     *
     * @param int $userId
     * @param DateTime $expiryDate
     * @return boolean
     */
    public function setUserExpiryDate($userId, $expiryDate) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if (!$record) {
            $record = new ExpireUsers_UserExpiryRecord();
            $record->userId = $userId;
        }
        $record->expiryDate = $expiryDate;
        return $record->save();
    }

    /**
     * Remove the expiry date record
     *
     * @param int $userId
     */
    public function clearUserExpiryDate($userId) {
        $record = ExpireUsers_UserExpiryRecord::model()->findByAttributes(array('userId' => $userId));
        if ($record) {
            $record->delete();
        }
    }

    /**
     * Checks to see if the user's status is suspended if after the expiry date
     * and sets the status to suspended if required
     *
     * @param int $userId
     */
    public function checkUserExpiryStatus($userId) {
        $expired = $this->shouldBeExpired($userId);
        if ($expired) {
            $user = craft()->users->getUserById($userId);
            if ($user->getStatus() !== UserStatus::Suspended) {
                craft()->users->suspendUser($user);
            }
        }
    }

    /**
     * Checks to see if the user should be expired
     *
     * @param int $userId
     * @return boolean
     */
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
}
