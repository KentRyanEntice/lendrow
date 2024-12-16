document.getElementById('showCashIn').addEventListener('click', function() {
    document.getElementById('overlayBgwallet').classList.add('active');
    document.getElementById('cashInForm').classList.add('active');
});

function hideCashInForm() {
    document.getElementById('overlayBgwallet').classList.remove('active');
    document.getElementById('cashInForm').classList.remove('active');
}

document.getElementById('showCashOut').addEventListener('click', function() {
    document.getElementById('overlayBgwallet').classList.add('active');
    document.getElementById('cashOutForm').classList.add('active');
});

function hideCashOutForm() {
    document.getElementById('overlayBgwallet').classList.remove('active');
    document.getElementById('cashOutForm').classList.remove('active');
}

document.getElementById('showRequest').addEventListener('click', function() {
	document.getElementById('transactionHistory').classList.remove('active');
    document.getElementById('requestHistory').classList.add('active');
});

document.getElementById('showTransaction').addEventListener('click', function() {
	document.getElementById('transactionHistory').classList.add('active');
    document.getElementById('requestHistory').classList.remove('active');
});

function showCashIn(id) {
	document.getElementById("overlayBgwallet").classList.toggle('active');
	document.getElementById("cashInCard" + id).classList.toggle('active');
}

function hideCashIn(id) {
	document.getElementById("overlayBgwallet").classList.remove('active');
	document.getElementById("cashInCard" + id).classList.remove('active');
}

function showReceipt(id) {
	document.getElementById("overlayBg2").classList.toggle('active');
	document.getElementById("receipt2" + id).classList.toggle('active');
}

function hideReceipt(id) {
	document.getElementById("overlayBg2").classList.remove('active');
	document.getElementById("receipt2" + id).classList.remove('active');
}

function showCashOut(id) {
	document.getElementById("overlayBgwallet").classList.toggle('active');
	document.getElementById("cashOutCard" + id).classList.toggle('active');
}

function hideCashOut(id) {
	document.getElementById("overlayBgwallet").classList.remove('active');
	document.getElementById("cashOutCard" + id).classList.remove('active');
}

function showCashOutReceipt(id) {
	document.getElementById("overlayBg2").classList.toggle('active');
	document.getElementById("cashOutReceipt" + id).classList.toggle('active');
}

function hideCashOutReceipt(id) {
	document.getElementById("overlayBg2").classList.remove('active');
	document.getElementById("cashOutReceipt" + id).classList.remove('active');
}
