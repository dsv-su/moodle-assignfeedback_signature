$(document).ready(function() {
    $('head').append(
        $('<link/>').attr(
            {
                'rel': 'stylesheet',
                'type': 'text/css',
                'href': 'feedback/signature/lib/jquery.signaturepad.css'
            }
        )
    );
    var form = $('#mform1');
    if (form.length) {
        var signature = $('canvas').html();
        if (signature) {
            form.signaturePad({displayOnly: true, lineWidth: 0, validateFields: false}).regenerate(signature);
        } else {
            form.signaturePad({drawOnly: true, lineWidth: 0, validateFields: false});
        }
    }
});
