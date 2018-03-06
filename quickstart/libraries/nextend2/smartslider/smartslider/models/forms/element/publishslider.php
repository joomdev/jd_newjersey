<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementPublishSlider extends N2ElementHidden {

    function fetchElement() {
        ob_start();
        ?>
        <script type="text/javascript">
            function selectText(container) {
                if (document.selection) {
                    var range = document.body.createTextRange();
                    range.moveToElementText(container);
                    range.select();
                } else if (window.getSelection) {
                    var range = document.createRange();
                    range.selectNode(container);
                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
                return false;
            }
        </script>
        <?php
        N2Base::getApplication('smartslider')
              ->getApplicationType('backend')
              ->getLayout()
              ->renderInlineInNamespace("publish", 'backend.inline', 'smartslider.platform', array(
                  'sliderid' => N2Get::getInt('sliderid')
              ));

        return ob_get_clean();
    }
}
