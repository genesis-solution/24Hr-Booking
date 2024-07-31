<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');
?>

<!------------------ calculator for num textinputs of the Posts Editor --------------------------->
<a href="javascript: wpbe_draw_calculator();void(0);" class="wpbe_calculator_btn"></a>
<div id="wpbe_calculator" style="display: none;">
    <table class="wpbe-full-width">
        <tr>
            <td>
                <select class="wpbe_calculator_operation">
                    <option value="+">+</option>
                    <option value="-">-</option>
                </select>
            </td>
            <td class="wpbe_calculator_value_td">
                <input type="number" value="" class="wpbe_calculator_value wpbe-full-width" placeholder="<?php esc_html_e('enter operation value', 'bulk-editor') ?>" />
            </td>
            <td>
                <select class="wpbe_calculator_how">
                    <option value="value">n</option>
                    <option value="percent">%</option>
                </select>
            </td>
            <td>
                <?php WPBE_HELPER::draw_rounding_drop_down() ?>
            </td>
            <td>
                <a href="#" class="wpbe_calculator_set button button-primary button-small"></a>&nbsp;<a href="#" class="wpbe_calculator_close button button-primary button-small"></a>
            </td>
        </tr>
    </table>

</div>

