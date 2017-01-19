<?php

namespace Craft;

class ExpireUsers_UserExpiryModel extends BaseModel {

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes() {
        return array_merge(parent::defineAttributes(), array(
            'userId' => array(AttributeType::Number),
            'expiryDate' => array(AttributeType::DateTime)
        ));
    }

}
