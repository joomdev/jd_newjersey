<?php

class N2ElementContainer extends N2Element
{

    function fetchElement() {

        return N2Html::tag('div', array(
            'id' => $this->_id
        ));
    }
}
