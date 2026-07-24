<div style="text-align: center;">
    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(120)->generate($guia->cadena_qr ?? 'Sin QR')) !!} ">
</div>