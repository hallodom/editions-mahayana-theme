<?php
/**
 * Single Event Template - Minimal Design for Éditions Mahayana
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_singular = tribe_get_event_label_singular();
$events_label_plural   = tribe_get_event_label_plural();

$event_id = Tribe__Events__Main::postIdHelper( get_the_ID() );

/**
 * Allows filtering of the event ID.
 *
 * @since 6.0.1
 *
 * @param numeric $event_id
 */
$event_id = apply_filters( 'tec_events_single_event_id', $event_id );

/**
 * Allows filtering of the single event template title classes.
 *
 * @since 5.8.0
 *
 * @param array   $title_classes List of classes to create the class string from.
 * @param numeric $event_id      The ID of the displayed event.
 */
$title_classes = apply_filters( 'tribe_events_single_event_title_classes', [ 'tribe-events-single-event-title', 'event-title' ], $event_id );
$title_classes = implode( ' ', tribe_get_classes( $title_classes ) );

/**
 * Allows filtering of the single event template title before HTML.
 *
 * @since 5.8.0
 *
 * @param string  $before   HTML string to display before the title text.
 * @param numeric $event_id The ID of the displayed event.
 */
$before = apply_filters( 'tribe_events_single_event_title_html_before', '<h1 class="' . $title_classes . '">', $event_id );

/**
 * Allows filtering of the single event template title after HTML.
 *
 * @since 5.8.0
 *
 * @param string  $after    HTML string to display after the title text.
 * @param numeric $event_id The ID of the displayed event.
 */
$after = apply_filters( 'tribe_events_single_event_title_html_after', '</h1>', $event_id );

/**
 * Allows filtering of the single event template title HTML.
 *
 * @since 5.8.0
 *
 * @param string  $title    HTML string to display. Return an empty string to not display the title.
 * @param numeric $event_id The ID of the displayed event.
 */
$title = apply_filters( 'tribe_events_single_event_title_html', the_title( $before, $after, false ), $event_id );
$cost  = tribe_get_formatted_cost( $event_id );

get_header();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Caveat:wght@400&display=swap');

/* Hide blockquotes until JavaScript processes them */
.event-content blockquote {
	display: none;
}

/* Show processed quotes */
.event-quote {
	display: block;
}

/* Back to events link */
.tribe-events-back {
	margin-bottom: 32px;
}

.tribe-events-back a {
	color: var(--color-text-secondary, #626c71);
	text-decoration: none;
	font-size: 0.95rem;
	transition: color 0.2s;
}

.tribe-events-back a:hover {
	color: var(--color-primary, #21808d);
}

/* Event notices */
.tribe-events-notices {
	margin-bottom: 24px;
}

/* Top navigation */
#tribe-events-header {
	margin-bottom: 32px;
}

.tribe-events-nav-pagination {
	margin: 0;
	padding: 0;
}

.tribe-events-sub-nav {
	display: flex;
	justify-content: space-between;
	list-style: none;
	margin: 0;
	padding: 12px 0;
	border-bottom: 1px solid rgba(94, 82, 64, 0.1);
	gap: 16px;
}

.tribe-events-sub-nav li {
	margin: 0;
	flex: 1;
}

.tribe-events-sub-nav a {
	color: var(--color-text-secondary, #626c71);
	text-decoration: none;
	font-size: 0.9rem;
	transition: color 0.2s;
	display: block;
}

.tribe-events-sub-nav a:hover {
	color: var(--color-primary, #21808d);
}

.tribe-events-nav-previous a::before {
	content: '';
	margin-right: 4px;
}

.tribe-events-nav-next {
	text-align: right;
}

.tribe-events-nav-next a::after {
	content: '';
	margin-left: 4px;
}

.event-container {
	max-width: 1000px;
	margin: 60px auto;
	padding: 0 24px;
}

.event-header {
	margin-bottom: 48px;
	padding-bottom: 32px;
	border-bottom: 1px solid rgba(94, 82, 64, 0.15);
}

.tribe-events-cost {
	display: inline-block;
	margin-left: 12px;
	padding: 4px 10px;
	background: var(--color-primary, #21808d);
	color: white;
	border-radius: 4px;
	font-weight: 500;
	font-size: 0.85rem;
	vertical-align: middle;
}

.event-title {
	font-size: 2.5rem;
	font-weight: 600;
	color: var(--color-text, #1f2121);
	margin: 0 0 24px 0;
	line-height: 1.2;
	letter-spacing: -0.02em;
}

.event-meta-bar {
	display: flex;
	gap: 32px;
	flex-wrap: wrap;
}

.event-meta-item {
	display: flex;
	align-items: center;
	gap: 8px;
	color: var(--color-text-secondary, #626c71);
	font-size: 0.95rem;
}

.event-meta-item svg {
	width: 20px;
	height: 20px;
	opacity: 0.7;
}

.event-featured-image {
	margin: 0 0 48px 0;
	border-radius: 12px;
	overflow: hidden;
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
	line-height: 0 !important;
	font-size: 0 !important;
}

.event-featured-image .tribe-events-event-image {
	display: block !important;
	line-height: 0 !important;
	font-size: 0 !important;
	padding: 0 !important;
	margin: 0 !important;
}

.event-featured-image img {
	width: 100% !important;
	height: auto !important;
	display: block !important;
	vertical-align: bottom !important;
	line-height: 0 !important;
	padding: 0 !important;
	margin: 0 !important;
}

.event-content {
	font-size: 1.05rem;
	line-height: 1.8;
	color: var(--color-text, #1f2121);
	margin-bottom: 48px;
}

.event-content img {
	max-width: 100%;
	height: auto;
}

.event-content > h2 {
	font-size: 1.75rem;
	font-weight: 600;
	margin-top: 48px;
	margin-bottom: 20px;
	color: var(--color-text, #1f2121);
	border-bottom: 2px solid rgba(33, 128, 141, 0.2);
	padding-bottom: 8px;
}

.event-content > h3 {
	font-size: 1.35rem;
	font-weight: 600;
	margin-top: 32px;
	margin-bottom: 16px;
	color: var(--color-text, #1f2121);
}

.event-content > p {
	margin-bottom: 20px;
}

.event-content > ul,
.event-content > ol {
	margin: 20px 0;
	padding-left: 24px;
}

.event-content > ul > li,
.event-content > ol > li {
	margin-bottom: 12px;
	line-height: 1.7;
}

/* Session callout boxes */
.event-content-section {
	background: #F9F9F4;
	border: 1px solid rgba(94, 82, 64, 0.12);
	border-radius: 4px;
	padding: 24px 28px;
	margin: 32px 0;
}

.event-content-section .session-title {
	margin: 0 0 16px 0;
	padding-left: 12px;
	border-left: 4px solid #B4A94D;
}

.event-content-section ul,
.event-content-section ol {
	margin: 0;
	padding-left: 24px;
}

.event-content-section li {
	margin-bottom: 10px;
	line-height: 1.7;
}

/* Quote blocks */
.event-quote {
	background: #F9F9F4;
	border: 1px solid rgba(94, 82, 64, 0.12);
	border-radius: 4px;
	padding: 28px 32px;
	margin: 32px 0;
	text-align: center;
}

.event-quote-text {
	font-family: 'Caveat', cursive;
	font-size: 2.5rem;
	line-height: 1.6;
	color: var(--color-text, #1f2121);
	margin: 0 0 8px 0;
	font-weight: 400;
}

.event-quote-author,
.event-quote-author * {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
	font-size: 0.95rem;
	color: var(--color-text-secondary, #626c71);
	font-style: normal;
	margin: 0;
}

/* Tickets section wrapper */
.event-tickets-wrapper {
	margin: 48px 0;
}

.event-tickets-wrapper h2 {
	font-size: 1.5rem;
	font-weight: 600;
	margin-bottom: 24px;
	color: var(--color-text, #1f2121);
}

/* Style the Event Tickets block output */
.tribe-tickets {
	background: rgba(252, 252, 249, 1);
	border: 1px solid rgba(94, 82, 64, 0.12);
	border-radius: 8px;
	padding: 24px;
}

.tribe-tickets__item {
	margin-bottom: 20px;
}

.tribe-tickets__item__title {
	font-size: 1.2rem;
	font-weight: 600;
	margin-bottom: 12px;
}

/* Hide the "Choisissez votre participation" label */
.tribe-tickets__tickets-item-extra-price label {
	display: none !important;
}

/* Layout for price section - use flexbox */
.tribe-tickets__tickets-item-extra-price {
	display: flex !important;
	flex-direction: row !important;
	gap: 20px !important;
	align-items: center !important;
}

.price.suggested-price {
	text-align: left !important;
	order: -1 !important;
	flex-shrink: 0 !important;
	font-size: 1rem;
	line-height: 1.6;
}

.tribe-tickets__tickets-item-extra-price > div {
	order: 1 !important;
	flex-grow: 1 !important;
}

.tribe-tickets input[type="text"],
.tribe-tickets input[type="number"] {
	padding: 10px 12px;
	border: 1px solid rgba(94, 82, 64, 0.2);
	border-radius: 4px;
	font-size: 1rem;
}

.tribe-tickets button,
.tribe-tickets .tribe-common-c-btn {
	padding: 12px 32px;
	background: var(--color-primary, #21808d);
	color: white;
	border: none;
	border-radius: 4px;
	cursor: pointer;
	font-weight: 500;
	transition: opacity 0.2s;
	font-size: 1rem;
}

.tribe-tickets button:hover,
.tribe-tickets .tribe-common-c-btn:hover {
	opacity: 0.8;
}

/* Event details card */
.event-details-card {
	background: rgba(252, 252, 249, 1);
	border: 1px solid rgba(94, 82, 64, 0.12);
	border-radius: 12px;
	padding: 32px;
	margin: 48px 0;
}

.event-details-title {
	font-size: 1.25rem;
	font-weight: 600;
	margin: 0 0 24px 0;
	color: var(--color-text, #1f2121);
}

.event-detail-row {
	display: grid;
	grid-template-columns: 140px 1fr;
	gap: 16px;
	padding: 16px 0;
	border-bottom: 1px solid rgba(94, 82, 64, 0.1);
}

.event-detail-row:last-child {
	border-bottom: none;
}

.event-detail-label {
	font-weight: 500;
	color: var(--color-text-secondary, #626c71);
	font-size: 0.9rem;
}

.event-detail-value {
	color: var(--color-text, #1f2121);
}

.event-detail-value a {
	color: var(--color-primary, #21808d);
	text-decoration: none;
	transition: opacity 0.2s;
}

.event-detail-value a:hover {
	opacity: 0.7;
}

/* Cost callout */
.event-cost {
	background: rgba(33, 128, 141, 0.08);
	border-left: 4px solid var(--color-primary, #21808d);
	padding: 20px 24px;
	margin: 32px 0;
	border-radius: 8px;
	font-size: 1.1rem;
	font-weight: 500;
}

/* Navigation */
.event-navigation-bottom {
	display: flex;
	justify-content: space-between;
	margin-top: 64px;
	padding-top: 32px;
	border-top: 1px solid rgba(94, 82, 64, 0.15);
	gap: 16px;
}

.event-nav-link {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 12px 24px;
	background: rgba(94, 82, 64, 0.08);
	border-radius: 8px;
	text-decoration: none;
	color: var(--color-text, #1f2121);
	font-weight: 500;
	transition: background 0.2s;
	font-size: 0.95rem;
}

.event-nav-link:hover {
	background: rgba(94, 82, 64, 0.15);
}

.event-nav-link.prev::before {
	content: '←';
	font-size: 1.2rem;
}

.event-nav-link.next::after {
	content: '→';
	font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 768px) {
	.event-container {
		margin: 32px auto;
		padding: 0 16px;
	}
	
	.event-title {
		font-size: 1.875rem;
	}
	
	.event-meta-bar {
		flex-direction: column;
		gap: 12px;
	}
	
	.tribe-events-sub-nav {
		flex-direction: column;
		gap: 8px;
	}
	
	.tribe-events-nav-next {
		text-align: left;
	}
	
	.event-detail-row {
		grid-template-columns: 1fr;
		gap: 8px;
	}
	
	.event-navigation-bottom {
		flex-direction: column;
	}
	
	.event-content-section,
	.event-quote {
		padding: 16px 20px;
	}
	
	.event-quote-text {
		font-size: 1.4rem;
	}
	
	.tribe-tickets__tickets-item-extra-price {
		flex-direction: column !important;
		align-items: flex-start !important;
	}
	
	.tribe-events-cost {
		display: block;
		margin: 12px 0 0 0;
	}
}

/* FORCE FOOTER FULL WIDTH - OVERRIDE THEME */
html body.single-tribe_events footer,
html body.single-tribe_events .footer,
html body.single-tribe_events .site-footer,
html body.single-tribe_events .site-content footer {
	max-width: 100vw !important;
	width: 100vw !important;
	margin-left: calc(-50vw + 50%) !important;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const content = document.querySelector('.event-content');
	if (!content) return;
	
	// 1. Session callouts: bold text + list
	const paragraphs = content.querySelectorAll('p');
	paragraphs.forEach(para => {
		const bold = para.querySelector('strong, b');
		
		if (bold) {
			const textContent = para.textContent.trim();
			const boldContent = bold.textContent.trim();
			
			if (boldContent.length > 0 && boldContent.length >= textContent.length * 0.8) {
				const nextElement = para.nextElementSibling;
				
				if (nextElement && (nextElement.tagName === 'UL' || nextElement.tagName === 'OL')) {
					const wrapper = document.createElement('div');
					wrapper.className = 'event-content-section';
					
					const sessionTitle = document.createElement('p');
					sessionTitle.className = 'session-title';
					sessionTitle.innerHTML = para.innerHTML;
					
					para.parentNode.insertBefore(wrapper, para);
					wrapper.appendChild(sessionTitle);
					wrapper.appendChild(nextElement);
					para.remove();
				}
			}
		}
	});
	
	// 2. Quote blocks with author detection
	const blockquotes = content.querySelectorAll('blockquote');
	blockquotes.forEach(blockquote => {
		const quoteWrapper = document.createElement('div');
		quoteWrapper.className = 'event-quote';
		
		let fullText = blockquote.innerHTML.trim();
		let quoteText = fullText;
		let authorText = '';
		
		const lines = fullText.split(/<br\s*\/?>/gi);
		
		for (let i = lines.length - 1; i >= 0; i--) {
			const lineText = lines[i].replace(/<[^>]*>/g, '').trim();
			
			if (lineText.startsWith('-')) {
				authorText = lineText.substring(1).trim();
				lines.splice(i, 1);
				quoteText = lines.join('<br>');
				break;
			}
		}
		
		if (!authorText) {
			const paragraphs = Array.from(blockquote.querySelectorAll('p'));
			
			if (paragraphs.length > 0) {
				const lastP = paragraphs[paragraphs.length - 1];
				const lastText = lastP.textContent.trim();
				
				if (lastText.startsWith('-')) {
					authorText = lastText.substring(1).trim();
					
					const quoteDiv = document.createElement('div');
					for (let i = 0; i < paragraphs.length - 1; i++) {
						quoteDiv.innerHTML += paragraphs[i].innerHTML;
						if (i < paragraphs.length - 2) {
							quoteDiv.innerHTML += '<br><br>';
						}
					}
					quoteText = quoteDiv.innerHTML;
				}
			}
		}
		
		const quoteDiv = document.createElement('div');
		quoteDiv.className = 'event-quote-text';
		quoteDiv.innerHTML = quoteText;
		
		quoteWrapper.appendChild(quoteDiv);
		
		if (authorText) {
			const authorDiv = document.createElement('div');
			authorDiv.className = 'event-quote-author';
			authorDiv.textContent = authorText;
			quoteWrapper.appendChild(authorDiv);
		}
		
		blockquote.parentNode.insertBefore(quoteWrapper, blockquote);
		blockquote.remove();
	});
	
	// 3. Round portrait images (auto-detect square images)
	const images = content.querySelectorAll('img');
	images.forEach(img => {
		img.addEventListener('load', function() {
			const aspectRatio = this.naturalWidth / this.naturalHeight;
			
			if (aspectRatio > 0.8 && aspectRatio < 1.2) {
				this.style.width = '200px';
				this.style.height = '200px';
				this.style.borderRadius = '50%';
				this.style.objectFit = 'cover';
				this.style.display = 'block';
				this.style.margin = '24px 0';
			}
		});
		
		if (img.complete) {
			img.dispatchEvent(new Event('load'));
		}
	});
});
</script>

<div class="event-container">
	
	<p class="tribe-events-back">
		<a href="<?php echo esc_url( tribe_get_events_link() ); ?>"> <?php printf( '&laquo; ' . esc_html_x( 'All %s', '%s Events plural label', 'the-events-calendar' ), $events_label_plural ); ?></a>
	</p>

	<!-- Notices -->
	<?php tribe_the_notices() ?>

	<!-- Event header with navigation -->
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>
		<nav class="tribe-events-nav-pagination" aria-label="<?php printf( esc_html__( '%s Navigation', 'the-events-calendar' ), $events_label_singular ); ?>">
			<ul class="tribe-events-sub-nav">
				<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '<span>&laquo;</span> %title%' ) ?></li>
				<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( '%title% <span>&raquo;</span>' ) ?></li>
			</ul>
		</nav>
	</div>

	<?php while ( have_posts() ) : the_post(); ?>
		
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			<header class="event-header">
				<?php echo $title; ?>
				
				<div class="event-meta-bar">
					<?php if ( function_exists( 'tribe_get_start_date' ) ) : ?>
						<div class="event-meta-item">
							<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
							</svg>
							<span>
								<?php 
								$start_date = tribe_get_start_date( null, false, 'j F Y' );
								$end_date = tribe_get_end_date( null, false, 'j F Y' );
								$start_time = tribe_get_start_date( null, false, 'H:i' );
								$end_time = tribe_get_end_date( null, false, 'H:i' );
								
								echo $start_date;
								
								// Show time if available
								if ( $start_time !== $end_time || $start_date !== $end_date ) {
									echo ' @ ' . $start_time;
								}
								
								// Show end date if different from start date
								if ( $start_date !== $end_date ) {
									echo ' - ' . $end_date . ' @ ' . $end_time;
								} elseif ( $start_time !== $end_time ) {
									echo ' - ' . $end_time;
								}
								?>
								<?php if ( ! empty( $cost ) ) : ?>
									<span class="tribe-events-cost"><?php echo esc_html( $cost ) ?></span>
								<?php endif; ?>
							</span>
						</div>
					<?php endif; ?>
					
					<?php if ( function_exists( 'tribe_get_venue' ) && tribe_get_venue() ) : ?>
						<div class="event-meta-item">
							<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
							</svg>
							<span><?php echo tribe_get_venue(); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="event-featured-image">
					<?php echo tribe_event_featured_image( $event_id, 'large', false ); ?>
				</div>
			<?php endif; ?>

			<!-- Event content -->
			<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
			<div class="event-content">
				<?php the_content(); ?>
			</div>
			<?php do_action( 'tribe_events_single_event_after_the_content' ) ?>

			<!-- Tickets will appear here via after_the_meta hook -->
			<?php do_action( 'tribe_events_single_event_after_the_meta' ) ?>
		</article>
		
		<nav class="event-navigation event-navigation-bottom" aria-label="<?php printf( esc_html__( '%s Navigation', 'the-events-calendar' ), $events_label_singular ); ?>">
			<?php if ( function_exists( 'tribe_get_prev_event_link' ) ) : ?>
				<?php 
				$prev = tribe_get_prev_event_link( '%title%' );
				if ( $prev ) : ?>
					<a href="<?php echo esc_url( tribe_get_prev_event_link() ); ?>" class="event-nav-link prev">
						Événement précédent
					</a>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if ( function_exists( 'tribe_get_next_event_link' ) ) : ?>
				<?php 
				$next = tribe_get_next_event_link( '%title%' );
				if ( $next ) : ?>
					<a href="<?php echo esc_url( tribe_get_next_event_link() ); ?>" class="event-nav-link next">
						Événement suivant
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</nav>

	<?php endwhile; // End of the loop ?>

</div><!-- .event-container -->

<?php get_footer(); ?>