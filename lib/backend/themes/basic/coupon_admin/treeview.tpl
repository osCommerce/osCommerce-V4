{$content}

<script>
	function closePopup() {
		$('.popup-box:last').trigger('popup.close');
		$('.popup-box-wrap:last').remove();
		return false;
}

</script>
<style>
	.dtree input[type="checkbox"] {
		margin: 0 5px 0 0;
		position: relative;
		top: 2px;
	}
	.dtree .holder {
		margin-bottom: 20px;
		border-top: 1px solid #d9d9d9;
		border-bottom: 1px solid #d9d9d9;
	}
	.dtree {
		font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
	}
</style>