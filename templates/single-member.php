<?php
if (!defined('ABSPATH')) exit;

$member_id = get_the_ID();
$first_name = get_post_meta($member_id, 'mdk_first_name', true);
$last_name = get_post_meta($member_id, 'mdk_last_name', true);
$email = get_post_meta($member_id, 'mdk_email', true);
$address = get_post_meta($member_id, 'mdk_address', true);
$color = get_post_meta($member_id, 'mdk_favorite_color', true);
$status = get_post_meta($member_id, 'mdk_status', true);
$teams = get_post_meta($member_id, 'mdk_teams', true);

get_header();
?>

<div class="mdk_member_container" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <h1><?php echo esc_html($first_name . ' ' . $last_name); ?></h1>

    <?php if (has_post_thumbnail()) : ?>
        <div style="margin-bottom: 1rem;">
            <?php the_post_thumbnail('large'); ?>
        </div>
    <?php endif; ?>

    <p><strong>Email:</strong> <?php echo esc_html($email); ?></p>
    <p><strong>Address:</strong> <?php echo esc_html($address); ?></p>
    <p><strong>Favorite Color:</strong>
        <span style="display:inline-block;width:20px;height:20px;background:<?php echo esc_attr($color); ?>;"></span>
        <?php echo esc_html($color); ?>
    </p>

    <?php if (!empty($teams)) : ?>
        <p><strong>Teams:</strong>
            <ul>
                <?php foreach ($teams as $team_id) :
                    $team_post = get_post($team_id);
                    if ($team_post) :
                        echo '<li>' . esc_html($team_post->post_title) . '</li>';
                    endif;
                endforeach; ?>
            </ul>
        </p>
    <?php endif; ?>

    <hr>

    <h2>Contact <?php echo esc_html($first_name); ?></h2>

    <?php if (isset($_GET['mdk_sent']) && $_GET['mdk_sent'] === '1') : ?>
        <div class="mdk_success_notice" style="padding:10px;background:#dff0d8;color:#3c763d;">
            Message sent successfully!
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <?php wp_nonce_field('mdk_contact_form', 'mdk_contact_nonce'); ?>
        <p>
            <label for="mdk_full_name">Your Name</label><br>
            <input type="text" name="mdk_full_name" id="mdk_full_name" required class="widefat">
        </p>
        <p>
            <label for="mdk_email">Your Email</label><br>
            <input type="email" name="mdk_email" id="mdk_email" required class="widefat">
        </p>
        <p>
            <label for="mdk_message">Message</label><br>
            <textarea name="mdk_message" id="mdk_message" rows="5" required class="widefat"></textarea>
        </p>
        <input type="hidden" name="mdk_member_id" value="<?php echo esc_attr($member_id); ?>">
        <p>
            <input type="submit" name="mdk_submit_contact" value="Send Message">
        </p>
    </form>
</div>

<?php get_footer(); ?>
