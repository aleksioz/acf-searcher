<?php

final class SearchForm {

	/**
	 * Render the search form
	 * 	
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output of the search form.
	 */
    public static function render($atts) {
		// Merge default attributes
		$atts = shortcode_atts([
			'category' => ''
		], $atts, 'acf_searcher');
	
		if (empty($atts['category'])) {
			return '<p>Kategorija je obavezna!</p>';
		}
	
		$fields = acf_get_fields('group_677f86d30fbf0'); // Retrieve ACF fields
	
		ob_start(); // Start output buffering, just for readability
		?>
	
		<div class="acf-searcher-instructions">
			<h4>Dobrodošli u pretragu oglasa! Molimo vas da koristite sledeće opcije za pretragu:</h4>
			<ul>
				<li><strong>Izborni elementi:</strong> Koristite padajuće menije i dugmiće za odabir rase, pola, veličine...</li>
				<li><strong>Datum:</strong> Unesite broj meseci za pretragu oglasa objavljenih u nazad toliko meseci.</li>
				<li><strong>Tekstualna pretraga:</strong> Unesite ključne reči koje se tačno pojavljuju u naslovu ili tekstu oglasa. Ovo može biti korisno samo ako znate tačan izraz koji tražite. Inače ostavite ovo polje prazno</li>
				<li><strong>Napomena:</strong> <i>Svako selektovano polje dodatno sužava pretragu, dok ostavljanje polja praznim proširuje pretragu.</i></li>
			</ul>
		</div>
	
		<form id="acf-search-form">    
			<input type="text" name="search" placeholder="Traži u tekstu i naslovu">
	
			<?php if (!empty($fields)): ?>
				<?php foreach ($fields as $field): ?>
					<?= self::renderField($field); ?>
				<?php endforeach; ?>
				<input type="hidden" name="category" value="<?= esc_attr($atts['category']) ?>">
			<?php endif; ?>
	
			<button type="submit">Potraži..</button>
		</form>
	
		<div id="acf-search-results"></div>
	
		<?php
		return ob_get_clean(); // Return buffered content
	}
	
	/**
	 * Render the field based on its type
	 *
	 * @param array $field The field data.
	 * @return void
	 */
	private static function renderField($field) {
		if ($field['type'] === 'select' || $field['type'] === 'radio'): ?>
			<select name="<?= esc_attr($field['name']) ?>" class="operator">
				<option value="" selected style="color: gray;">
					<?= esc_html($field['label']) ?>
				</option>
				<?php foreach ($field['choices'] as $value => $label): ?>
					<option value="<?= esc_attr($value) ?>">
						<?= esc_html($label) ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php elseif ($field['type'] === 'date_picker'): ?>
			<input type="number" name="<?= esc_attr($field['name']) ?>" placeholder="Period pretrage (meseci u nazad)" class="acf-searcher-date">
		<?php endif;
	}
	
} // End class