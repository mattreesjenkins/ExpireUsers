<?php

namespace Craft;

class ExpireUsersPlugin extends BasePlugin {

    public function getName() {
        return 'Expire Users';
    }

    public function getVersion() {
        return '0.1';
    }

    public function getDeveloper() {
        return 'Matt Jenkins';
    }

    public function getDeveloperUrl() {
        return 'http://www.rees-jenkins.co.uk/';
    }

    public function init() {

        $this->_setupEditEvent();
        $this->_setupEditHooks();
    }

    private function _setupEditEvent() {
        // EVENTS
        craft()->on('userSession.onBeforeLogin', function(Event $event) {
            $user = craft()->users->getUserByUsernameOrEmail($event->params['username']);
            $expired = craft()->expireUsers_userExpiry->shouldBeExpired($user->id);

            if ($expired) {
                if ($user->getStatus() !== UserStatus::Suspended) {
                    craft()->users->suspendUser($user);
                }
                $event->performAction = false;
            }
        });
    }

    private function _setupEditHooks() {
        // HOOKS
        craft()->templates->hook('cp.users.edit', function(&$context) {
            $expired = craft()->expireUsers_userExpiry->shouldBeExpired($context["userId"]);
            if ($expired) {
                $user = craft()->users->getUserById($context["userId"]);
                if ($user->getStatus() !== UserStatus::Suspended) {
                    craft()->users->suspendUser($user);

                    craft()->userSession->setNotice(null);
                    craft()->userSession->setError(Craft::t('Please change expiry date before unsuspending user.'));

                    $context['statusLabel'] = Craft::t('Suspended');
                    $statusActions = array();
                    if (craft()->userSession->checkPermission('administrateUsers')) {
                        $statusActions[] = array('action' => 'users/unsuspendUser', 'label' => Craft::t('Unsuspend'));
                    }
                    $context['actions'][0] = $statusActions;
                }
            }
        });

        // render the expiry date pane
        craft()->templates->hook('cp.users.edit.right-pane', function(&$context) {
            $expiryDate = craft()->expireUsers_userExpiry->getUserExpiryDate($context["userId"]);
            return craft()->templates->render("expireUsers/_includes/expirePane", array_merge($context, array('expiryDate' => $expiryDate)));
        });
    }

}
