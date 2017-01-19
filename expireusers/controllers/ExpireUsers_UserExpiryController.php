<?php

namespace Craft;

class ExpireUsers_UserExpiryController extends BaseController {

    /**
     * Updates the User's expiry date
     * @throws HttpException
     */
    public function actionUpdateExpiry() {
        $userId = craft()->request->getPost('userId');
        $expiryDate = (($expiryDate = craft()->request->getPost('expiryDate')) ? DateTime::createFromString($expiryDate, craft()->timezone) : null);
        if ($userId && $expiryDate) {

            // update the user's expiry date
            $result = craft()->expireUsers_userExpiry->setUserExpiryDate($userId, $expiryDate);
            if (!$result) {
                throw new HttpException(418, Craft::t('Error updating expiry date.'));
            }

            // check if the user's status is set correctly
            craft()->expireUsers_userExpiry->checkUserExpiryStatus($userId);

            craft()->userSession->setNotice(Craft::t('User expiry date updated.'));
        } else {
            // couldn't update the expiry, one of the values is invalid
            craft()->userSession->setError(Craft::t('Couldn’t save expiry date.'));
        }
        $this->redirectToPostedUrl();
    }

    /**
     * Clears the user's expiry date
     */
    public function actionClearExpiry() {
        $userId = craft()->request->getPost('userId');
        if ($userId) {
            craft()->expireUsers_userExpiry->clearUserExpiryDate($userId);
            craft()->userSession->setNotice(Craft::t('User expiry date cleared.'));
        }
        $this->redirectToPostedUrl();
    }

}
