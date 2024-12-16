document.getElementById('edit-pic').addEventListener('click', function() {
    document.getElementById('overlayBg').classList.add('active');
    document.getElementById('pictureForm').classList.add('active');
});

function hidePictureForm() {
    document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('pictureForm').classList.remove('active');
}

function logOut() {
	document.getElementById('overlayBg').classList.add('active');
    document.getElementById('logOutForm').classList.add('active');
}

function cancelLogOut() {
	document.getElementById('overlayBg').classList.remove('active');
    document.getElementById('logOutForm').classList.remove('active');
}