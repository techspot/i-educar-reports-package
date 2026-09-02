
function togglePdfFields() {
    var isCsv = $j("#formato").val() === 'csv';

    $j("#orientacao").closest('tr').toggle(!isCsv);
    $j("#emitir_assinaturas").closest('tr').toggle(!isCsv);
}

$j("#formato").on('change', togglePdfFields);

$j("#orientacao").on('click', function(){
	if($j("#orientacao").val() == 'paisagem'){
  		$j("#emitir_assinaturas").closest('tr').show();
	}else{
  		$j("#emitir_assinaturas").closest('tr').hide();
  	}
});

togglePdfFields();
