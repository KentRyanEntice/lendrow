var swiper = new Swiper(".slide-content", {
	slidesPerView: 1,
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
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("applyCard" + id).classList.add('active');
}

function hideCard(id) {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("applyCard" + id).classList.remove('active');
}

function showBorrowForm() {
	localStorage.setItem('activeBorrowerTab', 'activeBorrowTab');
	document.getElementById("borrowForm").classList.remove('active');
	document.getElementById("applicationManager").classList.remove('active');
    document.querySelector(".borrow-form-button").classList.add('active');
    document.querySelector(".borrow-form-button").classList.add('active');
	document.querySelector(".application-manager-button").classList.remove('active');
}

function showApplicationManager() {
	localStorage.setItem('activeBorrowerTab', 'activeApplicationTab');
	document.getElementById("borrowForm").classList.add('active');
	document.getElementById("applicationManager").classList.add('active');
	document.querySelector(".borrow-form-button").classList.remove('active');
	document.querySelector(".application-manager-button").classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('activeBorrowerTab');
    if (activeTab === 'activeBorrowTab') {
        showBorrowForm();
    } else if (activeTab === 'activeApplicationTab') {
        showApplicationManager();
    }
})

function showLending() {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("lendingDetails").classList.add('active');
}

function hideLending() {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("lendingDetails").classList.remove('active');
}

function showSearch() {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("searchForm").classList.add('active');
}

function hideSearch() {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("searchForm").classList.remove('active');
}

function showLender(id) {
	document.getElementById("overlayBg").classList.add('active');
	document.getElementById("lenderCard" + id).classList.add('active');
}

function hideLender(id) {
	document.getElementById("overlayBg").classList.remove('active');
	document.getElementById("lenderCard" + id).classList.remove('active');
}

function showAgreementCard(id) {
    document.getElementById("overlayBg2").classList.add('active');
    document.getElementById("agreementCard" + id).classList.add('active');
}

function hideAgreementCard(id) {
    document.getElementById("overlayBg2").classList.remove('active');
    document.getElementById("agreementCard" + id).classList.remove('active');
}

function showCancel(id) {
	document.getElementById('overlayCancel').classList.add('active');
    document.getElementById('cancelForm' + id).classList.add('active');
}

function cancel(id) {
	document.getElementById('overlayCancel').classList.remove('active');
    document.getElementById('cancelForm' + id).classList.remove('active');
}