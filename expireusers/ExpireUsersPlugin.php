<?php

namespace Craft;

class ExpireUsersPlugin extends BasePlugin {

    public function getName() {
        return 'Expire Users';
    }

    public function getVersion() {
        return '0.1.1';
    }

    public function getDeveloper() {
        return 'Matt Jenkins';
    }

    public function getDeveloperUrl() {
        return 'http://www.rees-jenkins.co.uk/';
    }

    public function init() {
        $this->_setupEvents();
        $this->_setupEditHooks();
    }

    /**
     * Listen for login events, intercept and check if the login is expired
     */
    private function _setupEvents() {
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

    /**
     * Sets up the required hooks for the user's edit page
     */
    private function _setupEditHooks() {
        // Initial hook to check status, this is required when actively modifying expiry date on the edit page
        craft()->templates->hook('cp.users.edit', function(&$context) {
            if ((int) $context["account"]->admin === 0) {
                $expired = craft()->expireUsers_userExpiry->shouldBeExpired($context["account"]->id);
                if ($expired) {
                    $user = craft()->users->getUserById($context["account"]->id);
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
            }
        });

        // render the expiry date pane
        craft()->templates->hook('cp.users.edit.right-pane', function(&$context) {
            if ((int) $context["account"]->admin === 0) {
                $expiryDate = craft()->expireUsers_userExpiry->getUserExpiryDate($context["account"]->id);
                return craft()->templates->render("expireUsers/_includes/expirePane", array_merge($context, array('expiryDate' => $expiryDate)));
            }
        });
    }

}
