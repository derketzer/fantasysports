$(document).ready(function() {
    if( $.fn.picklist ) {
        $( '#picklist-ex' ).picklist({
            addAllLabel: '<i class="icon-caret-right"></i><i class="icon-caret-right"></i>',
            addLabel: '<i class="icon-caret-right"></i>',
            removeAllLabel: '<i class="icon-caret-left"></i><i class="icon-caret-left"></i>',
            removeLabel: '<i class="icon-caret-left"></i>',
            sourceListLabel: "Opciones disponibles",
            targetListLabel: "Opciones seleccionadas"
        });
    }

    $('#tipo-boleto input:radio').change(function(){
        $('.boleto').hide();

        if($(this).val() == 1){
            $('#boleto-quiniela').show();
        }else if($(this).val() == 2){
            $('#boleto-marcador').show();
        }
    });
});