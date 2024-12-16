var swiper = new Swiper("#paymentManagerSlide .slide-content", {
	slidesPerView: 1,
	spaceBetween: 25,
	slidesPerGroup: 1,
	centerSlide: 'true',
	fade: 'true',
	grabCursor: 'true',
	loopFillGroupWithBlank: true,
		pagination: {
			el: "#paymentManagerSlide .swiper-pagination",
			clickable: true,
			dynamicBullets: true,
		},
		navigation: {
			nextEl: "#paymentManagerSlide .swiper-button-next",
			prevEl: "#paymentManagerSlide .swiper-button-prev",
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

var newSwiper = new Swiper("#collectManagerSlide .slide-content", {
	slidesPerView: 1,
	spaceBetween: 25,
	slidesPerGroup: 1,
	centerSlide: 'true',
	fade: 'true',
	grabCursor: 'true',
	loopFillGroupWithBlank: true,
		pagination: {
			el: "#collectManagerSlide .swiper-pagination",
			clickable: true,
			dynamicBullets: true,
		},
		navigation: {
			nextEl: "#collectManagerSlide .swiper-button-next",
			prevEl: "#collectManagerSlide .swiper-button-prev",
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

function showPayment() {
	localStorage.setItem('activePaymentTab', 'activePaymentManagerTab');
	document.getElementById("paymentManager").classList.add('active');
	document.getElementById("collectManager").classList.remove('active');
	document.querySelector(".pay-button").classList.add('active');
	document.querySelector(".collect-button").classList.remove('active');
}

function showCollect() {
	localStorage.setItem('activePaymentTab', 'activeCollectManagerTab');
	document.getElementById("paymentManager").classList.remove('active');
	document.getElementById("collectManager").classList.add('active');
	document.querySelector(".pay-button").classList.remove('active');
	document.querySelector(".collect-button").classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('activePaymentTab');
    if (activeTab === 'activePaymentManagerTab') {
        showPayment();
    } else if (activeTab === 'activeCollectManagerTab') {
        showCollect();
    }
})

function showLendingHistory() {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("lendingHistory").classList.add('active');
}

function hideLendingHistory() {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("lendingHistory").classList.remove('active');
}

function showLenderHistory() {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("lenderHistory").classList.add('active');
}

function hideLenderHistory() {
	document.getElementById("overlayBg").classList.toggle('active');
	document.getElementById("lenderHistory").classList.toggle('active');
}

function showPayment1(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm1' + id).classList.add('active');
}

function hidePayment1(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm1' + id).classList.remove('active');
}

function showPayment2(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm2' + id).classList.add('active');
}

function hidePayment2(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm2' + id).classList.remove('active');
}

function showPayment3(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm3' + id).classList.add('active');
}

function hidePayment3(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm3' + id).classList.remove('active');
}

function showPayment4(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm4' + id).classList.add('active');
}

function hidePayment4(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm4' + id).classList.remove('active');
}

function showPayment5(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm5' + id).classList.add('active');
}

function hidePayment5(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm5' + id).classList.remove('active');
}

function showPayment6(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm6' + id).classList.add('active');
}

function hidePayment6(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm6' + id).classList.remove('active');
}

function showPayment7(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm7' + id).classList.add('active');
}

function hidePayment7(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm7' + id).classList.remove('active');
}

function showPayment8(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm8' + id).classList.add('active');
}

function hidePayment8(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm8' + id).classList.remove('active');
}

function showPayment9(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm9' + id).classList.add('active');
}

function hidePayment9(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm9' + id).classList.remove('active');
}

function showPayment10(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm10' + id).classList.add('active');
}

function hidePayment10(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm10' + id).classList.remove('active');
}

function showPayment11(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm11' + id).classList.add('active');
}

function hidePayment11(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm11' + id).classList.remove('active');
}

function showPayment12(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('paymentForm12' + id).classList.add('active');
}

function hidePayment12(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('paymentForm12' + id).classList.remove('active');
}

function showReport(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('reportForm' + id).classList.add('active');
}

function hideReport(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('reportForm' + id).classList.remove('active');
}

function showUpdate1(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm1' + id).classList.add('active');
}

function hideUpdate1(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm1' + id).classList.remove('active');
}

function showUpdate2(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm2' + id).classList.add('active');
}

function hideUpdate2(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm2' + id).classList.remove('active');
}

function showUpdate3(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm3' + id).classList.add('active');
}

function hideUpdate3(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm3' + id).classList.remove('active');
}

function showUpdate4(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm4' + id).classList.add('active');
}

function hideUpdate4(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm4' + id).classList.remove('active');
}

function showUpdate5(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm5' + id).classList.add('active');
}

function hideUpdate5(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm5' + id).classList.remove('active');
}

function showUpdate6(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm6' + id).classList.add('active');
}

function hideUpdate6(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm6' + id).classList.remove('active');
}

function showUpdate7(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm7' + id).classList.add('active');
}

function hideUpdate7(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm7' + id).classList.remove('active');
}

function showUpdate8(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm8' + id).classList.add('active');
}

function hideUpdate8(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm8' + id).classList.remove('active');
}

function showUpdate9(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm9' + id).classList.add('active');
}

function hideUpdate9(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm9' + id).classList.remove('active');
}

function showUpdate10(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm10' + id).classList.add('active');
}

function hideUpdate10(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm10' + id).classList.remove('active');
}

function showUpdate11(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm11' + id).classList.add('active');
}

function hideUpdate11(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm11' + id).classList.remove('active');
}

function showUpdate12(id) {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('updateForm12' + id).classList.add('active');
}

function hideUpdate12(id) {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('updateForm12' + id).classList.remove('active');
}