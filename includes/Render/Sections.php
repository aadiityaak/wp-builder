<?php
/**
 * Section registry, catalog and renderers.
 *
 * @package WP_Builder
 */

namespace WPB\Render;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sections
 */
final class Sections {

	/**
	 * Section type specs: type => label + prop descriptions (used for the AI catalog).
	 *
	 * @var array
	 */
	const SPEC = array(
		'hero' => array(
			'label' => 'Hero (header besar halaman)',
			'props' => array(
				'title'      => 'Judul utama',
				'subtitle'   => 'Sub-judul / deskripsi singkat',
				'button_text' => 'Teks tombol (opsional)',
				'button_url' => 'URL tombol (opsional)',
				'bg'         => 'Warna latar hex, mis. "#1e293b"',
				'bg_image'   => 'URL gambar latar (opsional, gunakan Unsplash)',
				'text_align' => 'center | left',
				'text_color' => 'Warna teks hex (opsional)',
				'overlay'    => 'true jika perlu overlay gelap di atas bg_image agar teks terbaca',
			),
		),
		'heading' => array(
			'label' => 'Heading (judul section)',
			'props' => array(
				'text'       => 'Teks judul',
				'tag'        => 'h1 | h2 | h3 | h4',
				'text_align' => 'center | left | right',
				'text_color' => 'Warna hex (opsional)',
			),
		),
		'paragraph' => array(
			'label' => 'Paragraf (teks biasa)',
			'props' => array(
				'text'       => 'Isi teks (boleh beberapa baris)',
				'text_align' => 'center | left | right',
				'text_color' => 'Warna hex (opsional)',
			),
		),
		'button' => array(
			'label' => 'Tombol',
			'props' => array(
				'text'  => 'Teks tombol',
				'url'   => 'URL tujuan',
				'style' => 'primary | outline | ghost',
				'size'  => 'sm | md | lg',
				'align' => 'center | left | right',
			),
		),
		'image' => array(
			'label' => 'Gambar tunggal',
			'props' => array(
				'src'     => 'URL gambar (gunakan https://images.unsplash.com/...)',
				'alt'     => 'Teks alternatif',
				'align'   => 'center | left | right',
				'width'   => 'Lebar, mis. "60%" atau "800px"',
				'rounded' => 'true jika sudut membulat',
				'caption' => 'Keterangan di bawah gambar (opsional)',
			),
		),
		'gallery' => array(
			'label' => 'Galeri gambar (grid)',
			'props' => array(
				'images'  => 'Array objek: [{"src": "...", "alt": "..."}]',
				'columns' => '2 | 3 | 4',
				'rounded' => 'true untuk sudut membulat',
			),
		),
		'cards' => array(
			'label' => 'Kartu layanan / fitur (grid)',
			'props' => array(
				'title'    => 'Judul section',
				'subtitle' => 'Deskripsi singkat section (opsional)',
				'columns'  => '2 | 3 | 4',
				'cards'    => 'Array objek: [{"title": "...", "text": "...", "icon": "emoji opsional", "url": "opsional"}]',
			),
		),
		'cta' => array(
			'label' => 'Call-to-action (banner ajakan)',
			'props' => array(
				'title'       => 'Judul ajakan',
				'text'        => 'Deskripsi',
				'button_text' => 'Teks tombol',
				'button_url'  => 'URL tombol',
				'bg'          => 'Warna latar hex',
			),
		),
		'html' => array(
			'label' => 'HTML bebas (kode sendiri)',
			'props' => array(
				'html' => 'Raw HTML yang akan dirender (div, form, iframe, dsb). Tidak boleh <script>.',
			),
		),
		'columns' => array(
			'label' => 'Kolom sederhana (2-3 kolom teks)',
			'props' => array(
				'columns' => '2 | 3',
				'items'   => 'Array objek: [{"title": "...", "text": "..."}]',
			),
		),
		'spacer' => array(
			'label' => 'Spasi vertikal kosong',
			'props' => array(
				'height' => 'Tinggi piksel, mis. 60',
			),
		),
		'divider' => array(
			'label' => 'Garis pemisah',
			'props' => array(
				'width' => 'Lebar persen, mis. 80',
				'style' => 'solid | dashed | dotted',
			),
		),
		'map' => array(
			'label' => 'Peta (Google Maps embed)',
			'props' => array(
				'address' => 'Alamat / lokasi untuk dicari di peta',
				'height'  => 'Tinggi piksel, mis. 400',
			),
		),
		'video' => array(
			'label' => 'Video embed (YouTube/Vimeo)',
			'props' => array(
				'url'   => 'URL video (https://www.youtube.com/watch?v=...)',
				'title' => 'Judul video (untuk aksesibilitas)',
			),
		),
	);

	/**
	 * All section type slugs.
	 *
	 * @return array
	 */
	public static function types(): array {
		return array_keys( self::SPEC );
	}

	/**
	 * Human-readable catalog for the system prompt.
	 *
	 * @return string
	 */
	public static function catalog(): string {
		$lines   = array();
		$lines[] = 'Daftar tipe section yang tersedia beserta prop-nya (semua props opsional kecuali disebut wajib):';

		foreach ( self::SPEC as $type => $spec ) {
			$lines[] = sprintf( '- `%s` — %s.', $type, $spec['label'] );
			foreach ( $spec['props'] as $prop => $desc ) {
				$lines[] = sprintf( '  - `%s`: %s', $prop, $desc );
			}
		}

		$lines[] = '';
		$lines[] = 'Aturan konten:';
		$lines[] = '- Gunakan bahasa konten sesuai permintaan pengguna (umumnya Bahasa Indonesia).';
		$lines[] = '- Untuk gambar selalu gunakan URL Unsplash dengan parameter ukuran, contoh: https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&q=80 (jangan buat URL palsu).';
		$lines[] = '- Teks singkat, padat, profesional; jangan isi dengan placeholder lorem ipsum.';
		$lines[] = '- Warna latar (`bg`) gunakan hex modern yang konsisten dengan identitas yang diminta (mis. gelap #0f172a, terang #f8fafc, aksen #6366f1).';
		$lines[] = '- Jangan buat section lebih dari yang diperlukan; halaman yang baik punya 4-7 section.';

		return implode( "\n", $lines );
	}

	/**
	 * Render a section to HTML.
	 *
	 * @param array $section Section {id, type, props}.
	 * @param array $ctx     Context {is_preview, part}.
	 * @return string
	 */
	public static function render( array $section, array $ctx = array() ): string {
		$type = $section['type'] ?? '';
		$id   = isset( $section['id'] ) ? esc_attr( $section['id'] ) : '';

		if ( ! in_array( $type, self::types(), true ) ) {
			return '<section class="wpb-section wpb-unknown" data-wpb-id="' . $id . '"></section>';
		}

		$props = isset( $section['props'] ) && is_array( $section['props'] ) ? $section['props'] : array();
		$html  = call_user_func( array( __CLASS__, 'render_' . $type ), $props );

		// Wrap with section chrome (id, spacing, background).
		$class = 'wpb-section wpb-section-' . $type;
		$style = '';

		if ( ! empty( $props['bg'] ) ) {
			$style .= 'background-color:' . esc_attr( $props['bg'] ) . ';';
		}
		if ( ! empty( $props['bg_image'] ) && in_array( $type, array( 'hero', 'cta' ), true ) ) {
			$class .= ' wpb-section-bg-image';
			$style .= 'background-image:url(' . esc_url( $props['bg_image'] ) . ');';
		}

		$spacing = isset( $props['spacing'] ) ? $props['spacing'] : 'default';
		if ( ! in_array( $spacing, array( 'none', 'compact', 'default', 'wide' ), true ) ) {
			$spacing = 'default';
		}
		$class .= ' wpb-spacing-' . $spacing;

		return '<section class="' . $class . '" data-wpb-id="' . $id . '" style="' . $style . '">'
			. '<div class="wpb-container">' . $html . '</div>'
			. '</section>';
	}

	/**
	 * Render a part (header/footer) doc.
	 *
	 * @param array $doc Document.
	 * @param array $ctx Context.
	 * @return string
	 */
	public static function render_part( array $doc, array $ctx = array() ): string {
		$sections = $doc['sections'] ?? array();
		$html     = '';

		foreach ( $sections as $section ) {
			$html .= self::render( $section, $ctx );
		}

		return $html;
	}

	/**
	 * Hero.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_hero( array $p ): string {
		$align  = isset( $p['text_align'] ) && 'left' === $p['text_align'] ? 'left' : 'center';
		$color  = ! empty( $p['text_color'] ) ? ' style="color:' . esc_attr( $p['text_color'] ) . '"' : '';
		$button = '';

		if ( ! empty( $p['button_text'] ) && ! empty( $p['button_url'] ) ) {
			$button = '<div class="wpb-hero-actions"><a class="wpb-btn wpb-btn-primary" href="' . esc_url( $p['button_url'] ) . '">' . esc_html( $p['button_text'] ) . '</a></div>';
		}

		return '<div class="wpb-hero wpb-align-' . $align . '"' . $color . '>'
			. ( ! empty( $p['title'] ) ? '<h1 class="wpb-hero-title">' . esc_html( $p['title'] ) . '</h1>' : '' )
			. ( ! empty( $p['subtitle'] ) ? '<p class="wpb-hero-subtitle">' . esc_html( $p['subtitle'] ) . '</p>' : '' )
			. $button
			. '</div>';
	}

	/**
	 * Heading.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_heading( array $p ): string {
		$tag   = in_array( $p['tag'] ?? 'h2', array( 'h1', 'h2', 'h3', 'h4' ), true ) ? $p['tag'] : 'h2';
		$align = isset( $p['text_align'] ) ? esc_attr( $p['text_align'] ) : 'center';
		$style = ! empty( $p['text_color'] ) ? ' style="color:' . esc_attr( $p['text_color'] ) . '"' : '';

		return '<' . $tag . ' class="wpb-heading wpb-align-' . $align . '"' . $style . '>' . esc_html( $p['text'] ?? '' ) . '</' . $tag . '>';
	}

	/**
	 * Paragraph.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_paragraph( array $p ): string {
		$align = isset( $p['text_align'] ) ? esc_attr( $p['text_align'] ) : 'center';
		$style = ! empty( $p['text_color'] ) ? ' style="color:' . esc_attr( $p['text_color'] ) . '"' : '';

		return '<p class="wpb-paragraph wpb-align-' . $align . '"' . $style . '>' . nl2br( esc_html( $p['text'] ?? '' ) ) . '</p>';
	}

	/**
	 * Button.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_button( array $p ): string {
		$style = in_array( $p['style'] ?? 'primary', array( 'primary', 'outline', 'ghost' ), true ) ? $p['style'] : 'primary';
		$size  = in_array( $p['size'] ?? 'md', array( 'sm', 'md', 'lg' ), true ) ? $p['size'] : 'md';
		$align = isset( $p['align'] ) ? esc_attr( $p['align'] ) : 'center';

		return '<div class="wpb-align-' . $align . '"><a class="wpb-btn wpb-btn-' . $style . ' wpb-btn-' . $size . '" href="' . esc_url( $p['url'] ?? '#' ) . '">' . esc_html( $p['text'] ?? '' ) . '</a></div>';
	}

	/**
	 * Image.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_image( array $p ): string {
		if ( empty( $p['src'] ) ) {
			return '';
		}

		$class = 'wpb-image wpb-align-' . ( isset( $p['align'] ) ? esc_attr( $p['align'] ) : 'center' );
		if ( ! empty( $p['rounded'] ) ) {
			$class .= ' wpb-rounded';
		}

		$style = ! empty( $p['width'] ) ? ' style="width:' . esc_attr( $p['width'] ) . '"' : '';

		$html = '<div class="' . $class . '"><img src="' . esc_url( $p['src'] ) . '" alt="' . esc_attr( $p['alt'] ?? '' ) . '" loading="lazy"' . $style . ' />';
		if ( ! empty( $p['caption'] ) ) {
			$html .= '<p class="wpb-caption">' . esc_html( $p['caption'] ) . '</p>';
		}

		return $html . '</div>';
	}

	/**
	 * Gallery.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_gallery( array $p ): string {
		$images  = isset( $p['images'] ) && is_array( $p['images'] ) ? $p['images'] : array();
		$columns = in_array( $p['columns'] ?? 3, array( 2, 3, 4 ), true ) ? $p['columns'] : 3;
		$class   = 'wpb-gallery wpb-cols-' . $columns;
		if ( ! empty( $p['rounded'] ) ) {
			$class .= ' wpb-rounded';
		}

		$html = '<div class="' . $class . '">';
		foreach ( $images as $image ) {
			if ( empty( $image['src'] ) ) {
				continue;
			}
			$html .= '<figure><img src="' . esc_url( $image['src'] ) . '" alt="' . esc_attr( $image['alt'] ?? '' ) . '" loading="lazy" /></figure>';
		}

		return $html . '</div>';
	}

	/**
	 * Cards.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_cards( array $p ): string {
		$cards   = isset( $p['cards'] ) && is_array( $p['cards'] ) ? $p['cards'] : array();
		$columns = in_array( $p['columns'] ?? 3, array( 2, 3, 4 ), true ) ? $p['columns'] : 3;

		$html = '';
		if ( ! empty( $p['title'] ) ) {
			$html .= '<h2 class="wpb-heading wpb-align-center">' . esc_html( $p['title'] ) . '</h2>';
		}
		if ( ! empty( $p['subtitle'] ) ) {
			$html .= '<p class="wpb-paragraph wpb-align-center">' . esc_html( $p['subtitle'] ) . '</p>';
		}

		$html .= '<div class="wpb-cards wpb-cols-' . $columns . '">';
		foreach ( $cards as $card ) {
			$inner = '';
			if ( ! empty( $card['icon'] ) ) {
				$inner .= '<div class="wpb-card-icon">' . esc_html( $card['icon'] ) . '</div>';
			}
			$inner .= '<h3 class="wpb-card-title">' . esc_html( $card['title'] ?? '' ) . '</h3>';
			if ( ! empty( $card['text'] ) ) {
				$inner .= '<p class="wpb-card-text">' . nl2br( esc_html( $card['text'] ) ) . '</p>';
			}
			if ( ! empty( $card['url'] ) ) {
				$inner = '<a href="' . esc_url( $card['url'] ) . '">' . $inner . '</a>';
			}
			$html .= '<div class="wpb-card">' . $inner . '</div>';
		}

		return $html . '</div>';
	}

	/**
	 * CTA.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_cta( array $p ): string {
		$button = '';
		if ( ! empty( $p['button_text'] ) && ! empty( $p['button_url'] ) ) {
			$button = '<a class="wpb-btn wpb-btn-primary" href="' . esc_url( $p['button_url'] ) . '">' . esc_html( $p['button_text'] ) . '</a>';
		}

		return '<div class="wpb-cta">'
			. ( ! empty( $p['title'] ) ? '<h2 class="wpb-cta-title">' . esc_html( $p['title'] ) . '</h2>' : '' )
			. ( ! empty( $p['text'] ) ? '<p class="wpb-cta-text">' . esc_html( $p['text'] ) . '</p>' : '' )
			. $button
			. '</div>';
	}

	/**
	 * Raw HTML.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_html( array $p ): string {
		$allowed = wp_kses_allowed_html( 'post' );
		$allowed['iframe'] = array(
			'src'             => true,
			'title'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'loading'         => true,
		);

		return wp_kses( $p['html'] ?? '', $allowed );
	}

	/**
	 * Columns.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_columns( array $p ): string {
		$items   = isset( $p['items'] ) && is_array( $p['items'] ) ? $p['items'] : array();
		$columns = in_array( $p['columns'] ?? 2, array( 2, 3 ), true ) ? $p['columns'] : 2;

		$html = '<div class="wpb-columns wpb-cols-' . $columns . '">';
		foreach ( $items as $item ) {
			$html .= '<div class="wpb-column">'
				. ( ! empty( $item['title'] ) ? '<h3 class="wpb-column-title">' . esc_html( $item['title'] ) . '</h3>' : '' )
				. ( ! empty( $item['text'] ) ? '<p class="wpb-column-text">' . nl2br( esc_html( $item['text'] ) ) . '</p>' : '' )
				. '</div>';
		}

		return $html . '</div>';
	}

	/**
	 * Spacer.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_spacer( array $p ): string {
		$height = max( 4, (int) ( $p['height'] ?? 40 ) );
		return '<div class="wpb-spacer" style="height:' . $height . 'px"></div>';
	}

	/**
	 * Divider.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_divider( array $p ): string {
		$width = min( 100, max( 10, (int) ( $p['width'] ?? 80 ) ) );
		$style = in_array( $p['style'] ?? 'solid', array( 'solid', 'dashed', 'dotted' ), true ) ? $p['style'] : 'solid';
		return '<hr class="wpb-divider wpb-divider-' . $style . '" style="width:' . $width . '%" />';
	}

	/**
	 * Map.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_map( array $p ): string {
		if ( empty( $p['address'] ) ) {
			return '';
		}

		$height = max( 200, (int) ( $p['height'] ?? 400 ) );
		$src    = 'https://www.google.com/maps?q=' . rawurlencode( $p['address'] ) . '&output=embed';

		return '<div class="wpb-map" style="height:' . $height . 'px"><iframe src="' . esc_url( $src ) . '" loading="lazy" title="' . esc_attr( $p['address'] ) . '" allowfullscreen></iframe></div>';
	}

	/**
	 * Video.
	 *
	 * @param array $p Props.
	 * @return string
	 */
	private static function render_video( array $p ): string {
		$url = $p['url'] ?? '';
		if ( '' === $url ) {
			return '';
		}

		$embed = '';
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		if ( isset( $query['v'] ) && is_scalar( $query['v'] ) ) {
			$embed = 'https://www.youtube.com/embed/' . sanitize_text_field( (string) $query['v'] );
		} elseif ( str_contains( $url, 'youtu.be/' ) ) {
			$id    = sanitize_text_field( (string) substr( (string) wp_parse_url( $url, PHP_URL_PATH ), 1 ) );
			$embed = 'https://www.youtube.com/embed/' . $id;
		} elseif ( str_contains( $url, 'vimeo.com' ) ) {
			$id    = sanitize_text_field( (string) substr( (string) wp_parse_url( $url, PHP_URL_PATH ), 1 ) );
			$embed = 'https://player.vimeo.com/video/' . $id;
		}

		if ( '' === $embed ) {
			return '<div class="wpb-video"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Watch video', 'wp-builder' ) . '</a></div>';
		}

		return '<div class="wpb-video"><iframe src="' . esc_url( $embed ) . '" title="' . esc_attr( $p['title'] ?? '' ) . '" loading="lazy" allowfullscreen></iframe></div>';
	}
}
