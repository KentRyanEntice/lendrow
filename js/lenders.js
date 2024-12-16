function calculateMonthlyPayment() {
    var lendAmount = parseFloat(document.getElementById('amount').value.replace(/,/g, '')) || 0;
    var interestRate = parseFloat(document.getElementById('interest').value) || 0;
	var paymentTerm = parseFloat(document.getElementById('term').value) || 0;

	 if (lendAmount > 0 && interestRate > 0 && paymentTerm > 0) {
        var interestRatePerMonth = lendAmount * (interestRate / 100);
        var monthlyPayment = (lendAmount / paymentTerm) + interestRatePerMonth;
		
        document.getElementById('monthly').value = formatCurrency(monthlyPayment.toFixed(2));
    } else {
        document.getElementById('monthly').value = '';
    }
}

function formatCurrency(amount) {
    return amount.replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

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

function showLend() {
	document.getElementById("overlayLend").classList.add('active');
	document.getElementById("lend").classList.add('active');
}

function hideLend() {
	document.getElementById("overlayLend").classList.remove('active');
	document.getElementById("lend").classList.remove('active');
}

function showCard(id) {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("applyCard" + id).classList.add('active');
}

function hideCard(id) {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("applyCard" + id).classList.remove('active');
}

function showLendForm() {
	localStorage.setItem('activeLenderTab', 'activeLendTab');
	document.getElementById("lendForm").classList.remove('active');
	document.getElementById("lendManager").classList.remove('active');
	document.querySelector(".lend-form-button").classList.add('active');
	document.querySelector(".lend-manager-button").classList.remove('active');
}

function showLendManager() {
	localStorage.setItem('activeLenderTab', 'activeLendManagerTab');
	document.getElementById("lendForm").classList.add('active');
	document.getElementById("lendManager").classList.add('active');
	document.querySelector(".lend-manager-button").classList.add('active');
	document.querySelector(".lend-form-button").classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('activeLenderTab');
    if (activeTab === 'activeLendTab') {
        showLendForm();
    } else if (activeTab === 'activeLendManagerTab') {
        showLendManager();
    }
})

function showFundCard(id) {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("fundCard" + id).classList.add('active');
}

function hideFundCard(id) {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("fundCard" + id).classList.remove('active');
}

function showAgreementCard(id) {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("agreementCard" + id).classList.add('active');
}

function hideAgreementCard(id) {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("agreementCard" + id).classList.remove('active');
}

function showdate(id) {
	document.getElementById("date" + id).classList.add('active');
	document.getElementById("overlaycredit" + id).classList.add('active');
	document.getElementById("datebtn" + id).classList.add('active');
}

function hidedate(id) {
	document.getElementById("date" + id).classList.remove('active');
	document.getElementById("overlaycredit" + id).classList.remove('active');
	document.getElementById("datebtn" + id).classList.remove('active');
}

function printCard(id) {
	document.getElementById("overlayPrint").classList.add('active');
	document.getElementById("printCard" + id).classList.add('active');
}

function closePrint(id) {
	document.getElementById("overlayPrint").classList.remove('active');
	document.getElementById("printCard" + id).classList.remove('active');
}