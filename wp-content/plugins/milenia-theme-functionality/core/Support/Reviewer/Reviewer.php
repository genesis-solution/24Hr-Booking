<?php
/**
* Provides reviews functionality.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\Core\Support\Reviewer;

class Reviewer
{
    protected $post_types;

    protected $criterias;

    /**
     * Constructor.
     *
     * @param array $post_types
     * @param array $criterias
     * @access
     * @return
     */
    public function __construct($post_types = array('post'), $criterias)
    {
        $this->post_types = $post_types;
        $this->criterias = $criterias;

        add_action('comment_form_logged_in_after', array($this, 'addRatingFieldToCommentForm'));
        add_action('comment_form_after_fields', array($this, 'addRatingFieldToCommentForm'));
        add_action('comment_post', array($this, 'saveRatingData'));
    }

    public function getCommentRating($comment_id)
    {
        $total = 0;

        if(is_singular($this->post_types) && !empty($this->criterias)) {
            foreach($this->criterias as $criteria => $name) {
                $key = sprintf('comment_meta_%s', $criteria);
                $value = intval(get_comment_meta($comment_id, $key, true));
                if(!$value) continue;
                $total += $value;
            }
        }

        return $total / count($this->criterias);
    }

    /**
     * Adds rating fields to a comment form.
     *
     * @access public
     * @return void
     */
    public function addRatingFieldToCommentForm()
    {
        if(is_singular($this->post_types) && !empty($this->criterias)) : ?>

            <div class="form-group">
                <?php foreach($this->criterias as $criteria => $name) : ?>
                    <div class="from-col form-col-xl-3">
                        <label><?php echo esc_html($name) ?></label>

                        <div class="milenia-rating-field">
                            <div data-estimate="3" class="milenia-rating milenia-rating--independent"></div>
                            <input type="hidden" name="comment_meta_<?php echo esc_attr($criteria); ?>" value="3">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif;
    }

    /**
     * Returns array of criterias.
     *
     * @access public
     * @return array
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * Stores rating data in the database.
     *
     * @param int $comment_id
     * @access public
     * @return void
     */
    public function saveRatingData($comment_id)
    {
        if(!empty($this->criterias))
        {
            foreach($this->criterias as $criteria => $name)
            {
                $key = sprintf('comment_meta_%s', $criteria);

                if(isset($_POST[$key]) && !empty($_POST[$key]))
                {
                    $rating = wp_filter_nohtml_kses($_POST[$key]);
                    update_comment_meta( $comment_id, $key, $rating );
                }
            }
        }
    }

    public function getTotalEstimate($post_id, $from_criteria = null)
    {
        if(is_singular($this->post_types) && !empty($this->criterias))
        {
            $results = array();
            $total_estimate = 0;
            $total_count = 0;

            foreach ($this->criterias as $criteria => $name) $results[$criteria] = array();

            $comments = get_comments(array(
                'post_id' => $post_id
            ));

            if(is_array($comments) && !empty($comments))
            {
                foreach($comments as $comment)
                {
                    foreach($this->criterias as $criteria => $name)
                    {
                        $key = sprintf('comment_meta_%s', $criteria);
                        $value = intval(get_comment_meta($comment->comment_ID, $key, true));
                        if(!$value) continue;
                        array_push($results[$criteria], $value);
                    }
                }
            }

            if($from_criteria && isset($results[$from_criteria])) return  number_format((float) (array_sum($results[$from_criteria]) / count($results[$from_criteria])), 1, '.', '');

            foreach($results as $criteria => $values)
            {
                $total_count += count($values);
                $total_estimate += array_sum($values);
            }

            return number_format((float)$total_estimate / $total_count, 1, '.', '');
        }
    }

    public function getTotalEstimateName($post_id)
    {
        $total_estimate = $this->getTotalEstimate($post_id);

        if($total_estimate <= 1) {
            return esc_html_e('Awful', 'milenia-app-textdomain');
        }
        elseif($total_estimate <= 2) {
            return esc_html_e('Bad', 'milenia-app-textdomain');
        }
        elseif($total_estimate <= 3) {
            return esc_html_e('Normal', 'milenia-app-textdomain');
        }
        elseif($total_estimate <= 4) {
            return esc_html_e('Good', 'milenia-app-textdomain');
        }
        elseif($total_estimate <= 5) {
            return esc_html_e('Excellent', 'milenia-app-textdomain');
        }
    }
}
?>
