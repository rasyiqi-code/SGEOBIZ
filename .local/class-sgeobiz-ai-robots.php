<?php
/**
 * SGEOBIZ AI Robots.txt Agent
 *
 * Mengoptimalkan file robots.txt secara dinamis di WordPress untuk mengizinkan
 * perayapan (crawling) oleh agen AI populer dan tepercaya (seperti GPTBot,
 * Claude-Web, PerplexityBot, Applebot, dll.) agar konten situs dapat diindeks,
 * diproses, dan dikutip ke dalam pencarian generatif (GEO) secara legal dan aman.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_AI_Robots {

	/**
	 * Inisialisasi modul.
	 */
	public static function init() {
		$instance = new self();
		add_filter( 'robots_txt', [ $instance, 'add_ai_robots_rules' ], 99, 2 );
	}

	/**
	 * Sisipkan instruksi User-agent bot AI ke robots.txt WordPress secara dinamis.
	 *
	 * @param string $output Konten robots.txt default.
	 * @param bool   $public Apakah situs diatur sebagai publik.
	 * @return string Konten robots.txt termodifikasi.
	 */
	public function add_ai_robots_rules( $output, $public ) {
		// Jika situs diatur private/noindex oleh user, jangan paksa Allow AI bots
		if ( ! $public ) {
			return $output;
		}

		$ai_rules = "\n# SGEOBIZ AI Search Engine Agent (SEO 2026)\n";
		
		// Daftar crawler AI tepercaya yang ingin kita berikan akses penuh
		$ai_bots = [
			'GPTBot',            // OpenAI (ChatGPT)
			'Claude-Web',        // Anthropic (Claude)
			'ClaudeBot',         // Anthropic (Claude alternatif)
			'PerplexityBot',     // Perplexity AI
			'Google-Extended',   // Google AI / Gemini API
			'Applebot-Extended', // Apple AI
			'cohere-ai',         // Cohere LLM
		];

		foreach ( $ai_bots as $bot ) {
			$ai_rules .= "User-agent: " . $bot . "\n";
			$ai_rules .= "Allow: /\n\n";
		}

		return $output . $ai_rules;
	}
}
