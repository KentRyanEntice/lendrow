function fetchBalance() {
    var balance = document.getElementById('balance').value;

    if (balance.trim() !== '') {
        var xhr = new XMLHttpRequest();

        xhr.open('POST', 'php/fetchBalance.php', true);

        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.error) {
                    document.getElementById('balanceInfo').innerHTML = '<p>' + response.error + '</p>';
                } else {
                    document.getElementById('currentbalance').value = response.currentbalance;
                    document.getElementById('balanceInfo').innerHTML = '';
                }
            }
        };

        xhr.send('balance=' + encodeURIComponent(virtualBalance));
    }
}

function showAddMoneyForm() {
	localStorage.setItem('activeAdminTab', 'activeAddMoneyManagerTab');
	document.getElementById("addMoney").classList.add('active');
	document.getElementById("cashIn").classList.remove('active');
	document.getElementById("cashOut").classList.remove('active');
	document.querySelector(".addmoney-form-button").classList.add('active');
	document.querySelector(".cashin-manager-button").classList.remove('active');
	document.querySelector(".cashout-manager-button").classList.remove('active');
}

function showCashInManager() {
	localStorage.setItem('activeAdminTab', 'activeCashInManagerTab');
	document.getElementById("addMoney").classList.remove('active');
	document.getElementById("cashIn").classList.add('active');
	document.getElementById("cashOut").classList.remove('active');
	document.querySelector(".addmoney-form-button").classList.remove('active');
	document.querySelector(".cashin-manager-button").classList.add('active');
	document.querySelector(".cashout-manager-button").classList.remove('active');
}

function showCashOutManager() {
	localStorage.setItem('activeAdminTab', 'activeCashoutManagerTab');
	document.getElementById("addMoney").classList.remove('active');
	document.getElementById("cashIn").classList.remove('active');
	document.getElementById("cashOut").classList.add('active');
	document.querySelector(".addmoney-form-button").classList.remove('active');
	document.querySelector(".cashin-manager-button").classList.remove('active');
	document.querySelector(".cashout-manager-button").classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('activeAdminTab');
    if (activeTab === 'activeAddMoneyManagerTab') {
        showAddMoneyForm();
    } else if (activeTab === 'activeCashInManagerTab') {
        showCashInManager();
    } else if (activeTab === 'activeCashoutManagerTab') {
        showCashOutManager();
    }
})

function showCashIn(id) {
	document.getElementById("overlayBg1").classList.toggle('active');
	document.getElementById("cashInCard" + id).classList.toggle('active');
}

function hideCashIn(id) {
	document.getElementById("overlayBg1").classList.remove('active');
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

function showAddMoney(id) {
	document.getElementById("overlayBg3").classList.toggle('active');
	document.getElementById("cashIn" + id).classList.toggle('active');
}

function hideAddMoney(id) {
	document.getElementById("overlayBg3").classList.remove('active');
	document.getElementById("cashIn" + id).classList.remove('active');
}

function showCashOut(id) {
	document.getElementById("overlayBg1").classList.toggle('active');
	document.getElementById("cashOutCard" + id).classList.toggle('active');
}

function hideCashOut(id) {
	document.getElementById("overlayBg1").classList.remove('active');
	document.getElementById("cashOutCard" + id).classList.remove('active');
}

function showCashOutReceipt(id) {
	document.getElementById("overlayBg2").classList.toggle('active');
	document.getElementById("cashOutReceipt2" + id).classList.toggle('active');
}

function hideCashOutReceipt(id) {
	document.getElementById("overlayBg2").classList.remove('active');
	document.getElementById("cashOutReceipt2" + id).classList.remove('active');
}

function showLoadCashOut(id) {
	document.getElementById("overlayBg3").classList.toggle('active');
	document.getElementById("cashOut" + id).classList.toggle('active');
}

function hideLoadCashOut(id) {
	document.getElementById("overlayBg3").classList.remove('active');
	document.getElementById("cashOut" + id).classList.remove('active');
}

function showVirtualCashIn() {
	document.getElementById("virtualOverlay").classList.add('active');
	document.getElementById("virtualCashInForm").classList.add('active');
}

function hideVirtualCashIn() {
	document.getElementById("virtualOverlay").classList.remove('active');
	document.getElementById("virtualCashInForm").classList.remove('active');
}

function showVirtualSetUp() {
	document.getElementById("virtualOverlay").classList.add('active');
	document.getElementById("virtualSetUp").classList.add('active');
}

function hideVirtualSetUp() {
	document.getElementById("virtualOverlay").classList.remove('active');
	document.getElementById("virtualSetUp").classList.remove('active');
}

function showVirtualCashInHistory(id) {
	document.getElementById("virtualOverlay").classList.add('active');
	document.getElementById("addVirtualMoney" + id).classList.add('active');
}

function hideaddVirtualMoney(id) {
	document.getElementById("virtualOverlay").classList.remove('active');
	document.getElementById("addVirtualMoney" + id).classList.remove('active');
}

function showSystemBalance() {
	document.getElementById("systemBalance").classList.add('active');
	document.getElementById("virtualBalance").classList.remove('active');
}

function showVirtualBalance() {
	document.getElementById("systemBalance").classList.remove('active');
	document.getElementById("virtualBalance").classList.add('active');
}
