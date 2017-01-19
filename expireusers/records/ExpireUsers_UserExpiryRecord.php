<?php

namespace Craft;

class ExpireUsers_UserExpiryRecord extends BaseRecord {

    public function getTableName() {
        return 'expireusers_userexpiry';
    }

    protected function defineAttributes() {
        return array(
            'expiryDate' => array(AttributeType::DateTime)
        );
    }

    public function defineRelations() {
        return array(
            'user' => array(static::BELONGS_TO, 'UserRecord')
        );
    }

}
