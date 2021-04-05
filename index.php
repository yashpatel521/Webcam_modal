<!DOCTYPE html>
<html lang="en">

<head>
    <title>Webcam Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="croppie.js"></script>
    <link rel="stylesheet" href="croppie.css" />
    <style>
        video {
            width: 563px;
            height: 400px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Modal Webcam</h2>
        <!-- Trigger the modal with a button -->
        <button type="button" id="webcamModal" class="btn btn-info btn-lg">Open Modal</button>
        <canvas id="canvas" style="display:none;">
        </canvas>
        <div class="output">
            <img id="photo" alt="The screen capture will appear in this box.">
        </div>
        <!-- Modal -->
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="stopwebcam close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Modal Header</h4>
                    </div>
                    <div class="modal-body">
                        <video autoplay></video>
                    </div>
                    <div class="modal-footer">
                        <button id="TakePhoto" class="btn btn-info btn-lg">Upload Picture</button>
                        <button id="CropPhoto" class="btn btn-info btn-lg">Crop Picture</button>
                        <button type="button" class="stopwebcam btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div id="uploadimageModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Upload & Crop Image</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 text-center">
                            <div id="image_demo" style="width:350px; margin-top:30px"></div>
                        </div>
                        <div class="col-md-4" style="padding-top:30px;">
                            <br />
                            <br />
                            <br />
                            <button class="btn btn-success crop_image">Crop & Upload Image</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    var video = document.querySelector('video');
    var canvas = null;
    var width = 563;
    var height = 400;
    var stream, cropPhoto = 0,
        blobImage = {};
    $image_crop = $('#image_demo').croppie({
        enableExif: true,
        viewport: {
            width: 80,
            height: 80,
            type: 'circle' //square
        },
        boundary: {
            width: 300,
            height: 300
        }
    });

    document.getElementById('webcamModal').addEventListener('click', async (e) => {
        canvas = document.getElementById('canvas');
        try {
            var stream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: false
            }).then(function(stream) {
                $('#myModal').modal('show');
                video.srcObject = stream;
                video.play();
                stream.getVideoTracks()[0].onended = () => {
                    $('#myModal').modal('hide');
                    alert('someone unplugged the webcam');
                };
            })
        } catch (e) {
            console.error(e.name)
            switch (e.name) {
                case 'NotFoundError':
                    alert('Please setup your webcam first.');
                    break;
                case 'AbortError':
                    alert('Your webcam is busy.');
                    break;
                case 'NotReadableError':
                    alert('Your webcam is busy.');
                    break;
                case 'NotAllowedError':
                    alert('Permissin Denied.');
                    break;
                case 'SecurityError':
                    alert('Security error.');
                    break;
                case 'TypeError':
                    alert('Not secure Port.');
                    break;
                default:
                    alert('Something went Worng');
                    return;
            }
        }
    })

    document.getElementById('TakePhoto').addEventListener('click', function(ev) {
        cropPhoto = 0;
        takepicture();
        ev.preventDefault();
    }, false);

    jQuery('.stopwebcam').on('click', function() {
        stopwebcam();
    })

    function stopwebcam() {
        var track = video.srcObject.getTracks()[0];
        track.stop();
    }

    function takepicture() {
        var context = canvas.getContext('2d');
        if (width && height) {
            canvas.width = width;
            canvas.height = height;
            context.drawImage(video, 0, 0, width, height);

            if (cropPhoto == 1) {
                jQuery('#myModal').modal('hide');
                var data = canvas.toDataURL('image/png');

                $image_crop.croppie('bind', {
                    url: data
                }).then(function() {
                    console.log('jQuery bind complete');
                });

                stopwebcam();
                $('#uploadimageModal').modal('show');

                $('.crop_image').click(function(event) {
                    $image_crop.croppie('result', {
                        type: 'blob',
                        size: 'viewport',
                        circle: false
                    }).then(function(response) {
                        blobImage = response;
                        image = URL.createObjectURL(response, {
                            oneTimeOnly: true
                        });

                        photo.setAttribute('src', image);
                        $('#uploadimageModal').modal('hide');
                        uploadPhoto();
                    })
                })

            } else {
                var data = canvas.toDataURL('image/png');
                photo.setAttribute('src', data);
                jQuery('#myModal').modal('hide');
                blobImage = dataURItoBlob($('#photo').attr('src'));
                uploadPhoto();
            }
        }
    }

    $('#CropPhoto').on('click', function() {
        cropPhoto = 1;
        takepicture();
    })

    function uploadPhoto() {
        stopwebcam();
        var formData = new FormData();
        formData.append("photo", blobImage);
        $.ajax({
            url: "ajax.php",
            data: formData,
            type: "POST",
            contentType: false,
            processData: false,
            cache: false,
            error: function(err) {
                console.error(err);
            },
            success: function(data) {
                console.log(data);
            },
            complete: function() {
                console.log("Request finished.");
            }
        });
    }

    function dataURItoBlob(dataURI) {
        var byteString;
        if (dataURI.split(',')[0].indexOf('base64') >= 0)
            byteString = atob(dataURI.split(',')[1]);
        else
            byteString = unescape(dataURI.split(',')[1]);

        var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
        var ia = new Uint8Array(byteString.length);
        for (var i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }

        return new Blob([ia], {
            type: mimeString
        });
    }
</script>

</html>