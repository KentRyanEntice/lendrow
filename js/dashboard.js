var swiper = new Swiper(" .slide-content", {
	slidesPerView: 3,
	spaceBetween: 25,
	slidesPerGroup: 1,
	centerSlide: 'true',
	fade: 'true',
	grabCursor: 'true',
	loopFillGroupWithBlank: true,
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
			dynamicBullets: true,
		},
		navigation: {
			nextEl: ".swiper-button-next",
			prevEl: ".swiper-button-prev",
		},
		
		breakpoints: {
			0: {
				slidesPerView: 1,
			},
			520: {
				slidesPerView: 1,
			},
			950: {
				slidesPerView: 1,
			},
		},
});

function showCard(id) {
	document.getElementById("overlayBg").classList.toggle('active');
	document.getElementById("applyCard" + id).classList.toggle('active');
}

function hideCard(id) {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("applyCard" + id).classList.remove('active');
}