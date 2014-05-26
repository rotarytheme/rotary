<?php
/**
 *  Template Name: Speaker Archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); 
$args = array();
$args['wp_query'] = array('post_type' => 'rotary_speakers',
							'posts_per_page'  => -1,
							'orderby' => 'meta_value',
							'meta_key' => 'speaker_date');
$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'text',
						  'default' => '',
						  'compare' => 'LIKE',
						  'label' => 'First Name',
                          'meta_key' => 'speaker_first_name');
$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'text',
						  'compare' => 'LIKE',
                          'label' => 'Last Name',
                          'meta_key' => 'speaker_last_name');
$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'text',
						  'compare' => 'LIKE',
                          'label' => 'Organization',
                          'meta_key' => 'speaker_company');
$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'text',
                          'label' => 'Job Title/Position',
                          'compare' => 'LIKE',
                          'meta_key' => 'speaker_title');
               
$args['fields'][] = array('type' => 'taxonomy',
                          'label' => 'Category',
                          'format' => 'select',
                          'allow_null' => 'Select a Category',
                          'taxonomy' => 'rotary_speaker_cat');
                           
$args['fields'][] = array('type' => 'taxonomy',
                          'label' => 'Tag(s)',
                          'format' => 'select',
                          'allow_null' => 'Select a Tag',
                          'taxonomy' => 'rotary_speaker_tag');
                           
                          
$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'text',
                          'label' => 'About the Speaker',
                          'compare' => 'IN',
                          'meta_key' => 'speaker_bio');

$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'date',
                          'label' => 'Speaker Dates',
                          'compare' => '>=',
                          'data_type' => 'DATE',
                          'placeholder' => 'mm/dd/yyyy',
                          'meta_key' => 'speaker_date_from',
                          'sublabel' => 'From');
$args['fields'][] = array('type' => 'meta_key',
						  'format' => 'date',
                          'label' => '&nbsp;',
                          'sublabel' => 'To',
                          'compare' => '<=',
                          'data_type' => 'DATE',
                          'placeholder' => 'mm/dd/yyyy',
                          'meta_key' => 'speaker_date_to');

                       
$args['fields'][] = array('type' => 'reset',
						  'value' => "Reset Filters");
$args['fields'][] = array('type' => 'submit',
						  'value' => 'Search');
$args['form'] = array('method' => 'POST');


//$args['relevanssi'] = true;

//instantiate search
$speaker_search = new WP_Advanced_Search($args); ?>
<h1 class="pagetitle"><span>Speaker Program</span></h1>
<div id="speakertabs" class="speakertabs">
	 <ul>
<li id="archive-tab"><a href="#tabs-1">Archive</a></li>
<li id="upcoming-tab"><a href="<?php echo get_template_directory_uri() ?>/upcoming-programs.php">Upcoming Speakers</a></li>
</ul>

<div id="tabs-1">
<?php //show the form ?>
<a id="search-toggle" href="#" class="search-toggle collapsed">Advanced Search</a>
<?php 	$speaker_search->the_form(); ?>
<?php $query = new WP_Query();  
$query = $speaker_search->query();
?>

<div class="search-results">

<?php if ( $query->have_posts() ) : ?>
	<table class="speaker-archive-table" id="speaker-archive-table">
		<thead>
			<tr>
				<th>Speaker Link</th>
				<th>Date</th>
				<th>Program Title</th>
				<th>Name</th>
				<th>Job Title/Organization</th>
				<th>Category</th>
			</tr>
		</thead>

 	<?php  while ( $query->have_posts() ) : $query->the_post(); ?>
 			<?php $termField = ''; ?>
		    <?php $terms = wp_get_post_terms( get_the_id(), 'rotary_speaker_cat' ); ?> 
		    <?php //print_r($terms);?>
		    <?php if ($terms) : ?>
		    	 <?php foreach ($terms as $term) : ?>
		    	   <?php if ($term === end($terms)) : ?>
		    	   		<?php $termField .= $term->name; ?>
						<?php rotary_output_archive_table($termField); ?>	
					<?php else : ?>
						<?php $termField .= $term->name.', '; ?>
					<?php endif; ?>
				<?php endforeach; ?>
		    <?php else : ?>
		    		<?php rotary_output_archive_table(); ?>		
		    <?php endif; ?>
	<?php endwhile; ?>
	</table>
<?php else: ?>
<p class="nopseakers">There are no speakers that match your search criteria</p>
<?php endif;
	// Reset Post Data
	wp_reset_postdata();?>
</div><!--end div search-results-->
</div><!--end tab 2-->
</div> <!--end div speakertabs-->


<?php get_footer(); ?>