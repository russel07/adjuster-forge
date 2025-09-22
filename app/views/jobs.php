
<?php

$layout_class = $atts['layout'] === 'vertical' ? 'job-vertical' : 'job-horizontal';
?>
<div class="job <?php echo esc_attr($layout_class)?>">
    <div class="job-thumbnail">
        <img src="<?php echo esc_url($thumbnail); ?>" alt="Job Thumbnail">
    </div>
    <div class="job-content-wrapper">
    <div class="job-content">
        <h2 class="job-title"><?php echo get_the_title(); ?></h2>
        <div class="job-description">
            <?php echo esc_html( get_the_content() ); ?>
        </div>
        <div class="call-to-action">
            <a href="<?php echo esc_url($permalink); ?>" class="btn btn-primary">Apply Now</a>
        </div>
    </div>
    </div>
</div>