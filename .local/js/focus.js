/**
 * SGEOBIZ Focus JS
 * Analisis real-time kata kunci, kepadatan subjek, tautan, judul/deskripsi,
 * serta integrasi kamus sinonim & infleksi lokal.
 */

jQuery(document).ready(function($) {
	// Pastikan kita berada di editor postingan
	if ($('.sgeobiz-focus-container').length === 0) {
		return;
	}

	// State internal Focus
	const state = {
		activeSubjectIndex: 0,
		keywords: ['', '', ''],
		// Simpan saran sinonim & infleksi per kata kunci
		suggestions: [
			{ synonyms: [], inflections: [] },
			{ synonyms: [], inflections: [] },
			{ synonyms: [], inflections: [] }
		],
		timer: null
	};

	// DOM Elements
	const $container = $('.sgeobiz-focus-container');
	const $kwInputs = $('.sgeobiz-focus-kw-input');
	const $clearBtns = $('.sgeobiz-focus-kw-clear');
	const $tabButtonsWrap = $('.sgeobiz-focus-subject-tabs');
	const $tabButtons = $('.sgeobiz-focus-tab-btn');
	const $gaugeValue = $('.sgeobiz-focus-gauge-value');
	const $gaugeBar = $('.sgeobiz-focus-gauge-circle circle.bar');

	// Inisialisasi awal
	init();

	function init() {
		// Event handler input kata kunci
		$kwInputs.on('input', function() {
			const index = parseInt($(this).closest('.sgeobiz-focus-keyword-field').data('index'));
			const val = $(this).val().trim();
			state.keywords[index] = val;

			toggleClearButton($(this), val);
			updateSubjectTabsVisibility();

			// Debounce AJAX request kamus
			clearTimeout(state.timer);
			state.timer = setTimeout(() => {
				fetchSuggestions(index, val);
			}, 600);
		});

		// Event handler bersihkan kata kunci
		$clearBtns.on('click', function() {
			const $input = $(this).siblings('.sgeobiz-focus-kw-input');
			$input.val('').trigger('input');
		});

		// Switcher tab subjek
		$tabButtons.on('click', function() {
			$tabButtons.removeClass('active');
			$(this).addClass('active');
			state.activeSubjectIndex = parseInt($(this).data('target'));
			runAnalysis();
		});

		// Tampilkan tombol pembersih untuk input yang sudah terisi saat load
		$kwInputs.each(function() {
			const val = $(this).val().trim();
			const index = parseInt($(this).closest('.sgeobiz-focus-keyword-field').data('index'));
			state.keywords[index] = val;
			toggleClearButton($(this), val);
			if (val) {
				fetchSuggestions(index, val, true);
			}
		});

		updateSubjectTabsVisibility();

		// Pantau perubahan konten editor Gutenberg
		if (window.wp && wp.data && wp.data.subscribe) {
			wp.data.subscribe(() => {
				// Cegah overhead, lakukan analisis dengan throttling minimal
				runAnalysis();
			});
		}

		// Pantau perubahan meta title & description bawaan SGEOBIZ
		$(document).on('input change keyup', '#autodescription_title, #autodescription_description', function() {
			runAnalysis();
		});

		// Fallback Classic Editor (TinyMCE)
		setTimeout(() => {
			if (window.tinyMCE && window.tinyMCE.editors) {
				window.tinyMCE.editors.forEach(editor => {
					editor.on('keyup change NodeChange', () => {
						runAnalysis();
					});
				});
			}
			runAnalysis();
		}, 1500);
	}

	function toggleClearButton($input, val) {
		const $clearBtn = $input.siblings('.sgeobiz-focus-kw-clear');
		if (val.length > 0) {
			$clearBtn.show();
		} else {
			$clearBtn.hide();
		}
	}

	// Tampilkan navigation switcher jika minimal ada 2 kata kunci aktif
	function updateSubjectTabsVisibility() {
		const activeKeywordsCount = state.keywords.filter(kw => kw.length > 0).length;
		if (activeKeywordsCount >= 2) {
			$tabButtonsWrap.slideDown(200);
			// Pastikan subjek aktif saat ini valid
			if (state.keywords[state.activeSubjectIndex] === '') {
				// Cari kata kunci pertama yang tidak kosong
				const firstValidIndex = state.keywords.findIndex(kw => kw.length > 0);
				if (firstValidIndex !== -1) {
					state.activeSubjectIndex = firstValidIndex;
					$tabButtons.removeClass('active');
					$tabButtons.filter(`[data-target="${firstValidIndex}"]`).addClass('active');
				}
			}
		} else {
			$tabButtonsWrap.slideUp(200);
			state.activeSubjectIndex = state.keywords.findIndex(kw => kw.length > 0);
			if (state.activeSubjectIndex === -1) {
				state.activeSubjectIndex = 0;
			}
		}
		runAnalysis();
	}

	// Fetch sinonim & infleksi dari server
	function fetchSuggestions(index, keyword, isInit = false) {
		const $field = $(`.sgeobiz-focus-keyword-field[data-index="${index}"]`);
		const $loading = $field.find('.sgeobiz-focus-dict-loading');
		const $suggestions = $field.find('.sgeobiz-focus-suggestions');
		const $badgeList = $field.find('.sgeobiz-focus-badge-list');

		if (!keyword) {
			$suggestions.hide();
			$badgeList.empty();
			state.suggestions[index] = { synonyms: [], inflections: [] };
			runAnalysis();
			return;
		}

		$loading.show();

		$.ajax({
			url: sgeobizFocusL10n.ajax_url,
			method: 'GET',
			data: {
				action: 'sgeobiz_focus_dictionary',
				keyword: keyword,
				nonce: sgeobizFocusL10n.nonce
			},
			success: function(response) {
				$loading.hide();
				if (response.success && response.data) {
					const data = response.data;
					state.suggestions[index] = data;

					$badgeList.empty();
					const allVariations = [...new Set([...data.synonyms, ...data.inflections])];
					
					if (allVariations.length > 0) {
						allVariations.forEach(item => {
							$badgeList.append(`<span class="sgeobiz-focus-badge">${item}</span>`);
						});
						$suggestions.slideDown(200);
					} else {
						$suggestions.hide();
					}
				}
				runAnalysis();
			},
			error: function() {
				$loading.hide();
			}
		});
	}

	// Mengambil teks bersih dari editor
	function getCleanContent() {
		let content = '';

		// Gutenberg Block Editor
		if (window.wp && wp.data && wp.data.select('core/editor')) {
			content = wp.data.select('core/editor').getEditedPostContent() || '';
		}

		// Classic Editor TinyMCE
		if (!content && window.tinyMCE && window.tinyMCE.get('content')) {
			content = window.tinyMCE.get('content').getContent() || '';
		}

		// Textarea biasa
		if (!content) {
			content = $('#content').val() || '';
		}

		// Hilangkan semua tag HTML & ganti entity character
		return content
			.replace(/<[^>]*>/g, ' ')
			.replace(/&nbsp;/g, ' ')
			.replace(/\s+/g, ' ')
			.trim();
	}

	// Jalankan Analisis SEO Real-Time
	function runAnalysis() {
		const keyword = state.keywords[state.activeSubjectIndex] || '';
		const content = getCleanContent();
		
		// Jika kata kunci kosong, set skor 0 dan reset status
		if (!keyword) {
			resetChecklist();
			updateGauge(0);
			return;
		}

		const totalWords = countWords(content);
		const variations = getKeywordVariations(state.activeSubjectIndex, keyword);
		
		// Hitung kemunculan kata kunci + sinonim/infleksinya
		let keywordCount = 0;
		variations.forEach(variation => {
			const regex = new RegExp('\\b' + escapeRegExp(variation) + '\\b', 'gi');
			const matches = content.match(regex);
			if (matches) {
				keywordCount += matches.length;
			}
		});

		// 1. Kepadatan Kata Kunci (Subject Density)
		const density = totalWords > 0 ? (keywordCount / totalWords) * 100 : 0;
		let densityScore = 0;
		let densityStatus = 'error';

		if (density === 0) {
			densityScore = 0;
			densityStatus = 'error';
		} else if (density < 1.0) {
			densityScore = 50;
			densityStatus = 'warning';
		} else if (density >= 1.0 && density <= 2.5) {
			densityScore = 100;
			densityStatus = 'success';
		} else {
			// Keyword stuffing (> 2.5%)
			densityScore = 50;
			densityStatus = 'warning';
		}
		
		updateChecklistItem('density', densityStatus, `${density.toFixed(1)}% (${keywordCount} kata)`, densityStatus === 'success' ? 'Kepadatan subjek ideal.' : (density > 2.5 ? 'Terlalu padat (stuffed). Gunakan sinonim secara alami.' : 'Kepadatan terlalu rendah. Cantumkan subjek lebih sering.'));

		// 2. Kata Kunci di Meta Title
		const metaTitle = $('#autodescription_title').val() || '';
		const titleContains = checkContains(metaTitle, variations);
		updateChecklistItem('title', titleContains ? 'success' : 'error', '', titleContains ? 'Kata kunci ditemukan di Judul Meta.' : 'Kata kunci belum ada di Judul Meta.');

		// 3. Kata Kunci di Meta Description
		const metaDesc = $('#autodescription_description').val() || '';
		const descContains = checkContains(metaDesc, variations);
		updateChecklistItem('description', descContains ? 'success' : 'error', '', descContains ? 'Kata kunci ditemukan di Deskripsi Meta.' : 'Kata kunci belum ada di Deskripsi Meta.');

		// 4. Kata Kunci di Paragraf Pertama
		const firstParagraph = getFirstParagraph(content);
		const introContains = checkContains(firstParagraph, variations);
		updateChecklistItem('intro', introContains ? 'success' : 'warning', '', introContains ? 'Kata kunci ditemukan di paragraf pembuka.' : 'Paragraf pembuka sebaiknya mengandung subjek.');

		// 5 & 6. Linking (Internal & External)
		const links = getLinksInfo();
		const internalStatus = links.internal > 0 ? 'success' : 'warning';
		updateChecklistItem('linking-internal', internalStatus, `${links.internal} tautan`, internalStatus === 'success' ? 'Tautan internal memadai.' : 'Tambahkan minimal 1 tautan internal.');
		
		const externalStatus = links.external > 0 ? 'success' : 'warning';
		updateChecklistItem('linking-external', externalStatus, `${links.external} tautan`, externalStatus === 'success' ? 'Tautan eksternal memadai.' : 'Tambahkan minimal 1 tautan eksternal.');

		// 7. Panjang Konten
		let lengthScore = 0;
		let lengthStatus = 'error';
		if (totalWords < 300) {
			lengthScore = 0;
			lengthStatus = 'error';
		} else if (totalWords >= 300 && totalWords < 600) {
			lengthScore = 50;
			lengthStatus = 'warning';
		} else {
			lengthScore = 100;
			lengthStatus = 'success';
		}
		updateChecklistItem('length', lengthStatus, `${totalWords} kata`, lengthStatus === 'success' ? 'Panjang konten optimal.' : 'Tulis minimal 300 kata.');

		// Hitung Total Skor
		const titleScore = titleContains ? 100 : 0;
		const descScore = descContains ? 100 : 0;
		const introScore = introContains ? 100 : 50;
		const internalScore = links.internal > 0 ? 100 : 50;
		const externalScore = links.external > 0 ? 100 : 50;

		const totalScore = Math.round((densityScore + titleScore + descScore + introScore + internalScore + externalScore + lengthScore) / 7);
		updateGauge(totalScore);

		// Berikan tanda visual (active) pada badge sinonim jika ia digunakan di konten
		updateUsedBadges(variations, content);
	}

	function countWords(str) {
		if (!str) return 0;
		return str.split(/\s+/).filter(word => word.length > 0).length;
	}

	function getKeywordVariations(index, keyword) {
		const list = [keyword];
		const suggestions = state.suggestions[index];
		if (suggestions) {
			if (suggestions.synonyms) list.push(...suggestions.synonyms);
			if (suggestions.inflections) list.push(...suggestions.inflections);
		}
		// Saring nilai unik dan kosong
		return [...new Set(list.map(v => v.trim().toLowerCase()).filter(v => v.length > 0))];
	}

	function checkContains(text, variations) {
		if (!text) return false;
		text = text.toLowerCase();
		return variations.some(variation => text.includes(variation));
	}

	function getFirstParagraph(text) {
		// Asumsi paragraf pertama dibatasi spasi ganda atau hingga 150 karakter awal
		const words = text.split(/\s+/).slice(0, 45);
		return words.join(' ');
	}

	function getLinksInfo() {
		let rawHtml = '';
		// Gutenberg
		if (window.wp && wp.data && wp.data.select('core/editor')) {
			rawHtml = wp.data.select('core/editor').getEditedPostContent() || '';
		}
		// Classic Editor TinyMCE
		if (!rawHtml && window.tinyMCE && window.tinyMCE.get('content')) {
			rawHtml = window.tinyMCE.get('content').getContent() || '';
		}

		if (!rawHtml) {
			return { internal: 0, external: 0 };
		}

		// Parse link
		const parser = new DOMParser();
		const doc = parser.parseFromString(rawHtml, 'text/html');
		const anchors = doc.querySelectorAll('a');
		const currentHost = window.location.host;

		let internal = 0;
		let external = 0;

		anchors.forEach(a => {
			const href = a.getAttribute('href');
			if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
				if (href.includes(currentHost) || href.startsWith('/') || href.startsWith('.') || !href.includes('://')) {
					internal++;
				} else {
					external++;
				}
			}
		});

		return { internal, external };
	}

	// Update Tampilan Indikator Evaluasi
	function updateChecklistItem(id, status, metricText, descriptionText) {
		const $item = $(`#sgeobiz-check-${id}`);
		$item.removeClass('status-error status-warning status-success').addClass(`status-${status}`);
		
		if (metricText !== undefined && metricText !== '') {
			$item.find('.check-metric').text(metricText).show();
		} else {
			$item.find('.check-metric').hide();
		}

		if (descriptionText) {
			$item.find('.check-desc').text(descriptionText);
		}
	}

	// Tandai badge sinonim yang digunakan
	function updateUsedBadges(variations, content) {
		const contentLower = content.toLowerCase();
		$container.find('.sgeobiz-focus-badge').each(function() {
			const text = $(this).text().trim().toLowerCase();
			if (contentLower.includes(text)) {
				$(this).addClass('active').attr('title', 'Variasi kata ini telah Anda gunakan');
			} else {
				$(this).removeClass('active').removeAttr('title');
			}
		});
	}

	function resetChecklist() {
		$('.sgeobiz-focus-check-item').removeClass('status-error status-warning status-success');
		$('.sgeobiz-focus-check-item .check-metric').hide();
	}

	// Update Tampilan Radial Gauge Skor
	function updateGauge(score) {
		$gaugeValue.text(`${score}%`);
		
		// Ubah dashoffset: 283 adalah 100%, 0 adalah 0%
		const dashoffset = 283 - (283 * score) / 100;
		$gaugeBar.css('stroke-dashoffset', dashoffset);

		// Ubah warna bar berdasarkan skor
		if (score < 40) {
			$gaugeBar.css('stroke', '#ef4444'); // Merah
		} else if (score < 80) {
			$gaugeBar.css('stroke', '#f97316'); // Oranye
		} else {
			$gaugeBar.css('stroke', '#22c55e'); // Hijau
		}
	}

	function escapeRegExp(string) {
		return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}
});
