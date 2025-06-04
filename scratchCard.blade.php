@extends('layouts.strides')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    const hasGift = @json(!!($gift && $gift->media)); 
</script>


<script>
window.addEventListener('DOMContentLoaded', function () {
    
    let canvas = document.getElementById('scratchCanvas');
     
    if (!canvas) return; 

    let ctx = canvas.getContext('2d', { willReadFrequently: true });
    let isDrawing = false;
    let revealed = false;

    let coverImage = new Image();
    coverImage.src = '/images/scratch-card.jpg';

    coverImage.onload = function () {
        ctx.drawImage(coverImage, 0, 0, canvas.width, canvas.height);
        ctx.globalCompositeOperation = 'destination-out';
    };

    const movementThreshold = 5;
    let lastTouch = { x: null, y: null };
    let lastMouse = { x: null, y: null };

    // Touch events
    canvas.addEventListener('touchstart', (e) => {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches[0];
        lastTouch.x = touch.clientX - rect.left;
        lastTouch.y = touch.clientY - rect.top;
    });

    canvas.addEventListener('touchend', () => {
        isDrawing = false;
        lastTouch = { x: null, y: null };
    });

    canvas.addEventListener('touchcancel', () => {
        isDrawing = false;
        lastTouch = { x: null, y: null };
    });

    canvas.addEventListener('touchmove', (e) => {
        if (!isDrawing) return;
        e.preventDefault();
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches[0];
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;

        const dx = x - lastTouch.x;
        const dy = y - lastTouch.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > movementThreshold) {
            scratch({ clientX: touch.clientX, clientY: touch.clientY });
            lastTouch.x = x;
            lastTouch.y = y;
        }
    });

    // Mouse events
    canvas.addEventListener('mousedown', (e) => {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        lastMouse.x = e.clientX - rect.left;
        lastMouse.y = e.clientY - rect.top;
    });

    canvas.addEventListener('mouseup', () => {
        isDrawing = false;
        lastMouse = { x: null, y: null };
    });

    canvas.addEventListener('mouseleave', () => {
        isDrawing = false;
        lastMouse = { x: null, y: null };
    });

    canvas.addEventListener('mousemove', (e) => {
        if (!isDrawing) return;
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const dx = x - lastMouse.x;
        const dy = y - lastMouse.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > movementThreshold) {
            scratch(e);
            lastMouse.x = x;
            lastMouse.y = y;
        }
    });

    function scratch(e) {
        if (!isDrawing) return;

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        ctx.beginPath();
        ctx.arc(x, y, 20, 0, Math.PI * 2);
        ctx.fill();

        if (!revealed && getScratchedPercentage() > 20) {
            revealed = true;

            // Fade out canvas
            canvas.style.transition = 'opacity 1s ease';
            canvas.style.opacity = '0';

            // Show sprinkle overlay
            const sprinkle = document.getElementById('sprinkleOverlay');
            if (sprinkle) {
                sprinkle.style.display = 'block';
            }

            // Launch confetti
             if (hasGift) {
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
            // Hide sprinkle and reload after 5s
            setTimeout(() => {
                if (sprinkle) sprinkle.style.display = 'none';
                location.reload();
            }, 5000);

            // Save scratch result to server
            fetch("/save-scratch-card", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    employee_id: {{ $employee->id }},
                    company_id: {{ $employee->company_id }},
                    gift_id: {{ $gift->id ?? 'null' }}
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log(data.message);
            })
            .catch(err => {
                console.error('Error saving scratch data:', err);
            });
        }
    }

    function getScratchedPercentage() {
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        let totalPixels = imageData.data.length / 4;
        let transparentPixels = 0;

        for (let i = 0; i < imageData.data.length; i += 4) {
            if (imageData.data[i + 3] === 0) {
                transparentPixels++;
            }
        }

        return (transparentPixels / totalPixels) * 100;
    }
});
</script>

@endpush
@push('styles')
<style>
    #app, body {
        background-image: url("{{ asset('images/background-image.jpg') }}") !important;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        margin: 0 !important;
        padding: 0 !important;
        height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-direction: column;
    }

    .scratch-page {
        margin-top: 0;
        width: 100%;
        height: 100%;
        /* position: relative; */
    }

    .title {
        text-align: center;
        color: white;
        margin-bottom: 50px;
        font-size: 3rem;
        padding:10px;
    }

    #scratchCanvas {
        max-width: 100%;
        height: auto;
        display: block;
    }

   .scratch-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        
        box-sizing: border-box;
        text-align: center;
        height: 100%;
    }

    #scratch-area {
        width: 90%;
        max-width: 500px;
        aspect-ratio: 5 / 3;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #scratch-area img#giftImage {
        z-index: 1;
        position: absolute;
        top: 5%;
        left: 5%;
        width: 90%;
        height: 90%;
        object-fit: contain;
        border-radius: 8px;
    }
    
  #sprinkleOverlay {
        position: absolute;
        top: 0;
        width: 100vw; 
        height: 100vh; 
        display: none;
    
    }

    canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: 100% !important;
        /* border-radius: 10px; */
        touch-action: none;
        z-index: 3;
        transition: opacity 1s ease;
    }

    .scratch-gift {
        display: none; 
        margin-top: 30px;
    }
    .sprinkle {
        z-index: 9998;
        pointer-events: none;
    }


    #sprinkleOverlay img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #confettiCanvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10000;
    }

    #finalPopup {
        background-color: white;
        padding: 40px 5vw;             
        border-radius: 30px;
        width: 90%;                     
        max-width: 600px;               
        box-sizing: border-box;        
        margin: 0 auto;                 
    }

    @keyframes sprinkleFadeOut {
        to {
            opacity: 0;
            display: none;
        }
    }

    footer {
        display: none !important;
    }

 
   #finalPopup .popup-content h2,
    #finalPopup .popup-content h3 {
        font-size: 1.5rem; /* Smaller titles */
        margin: 0.5em 0;
        padding: 5px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }


    #finalPopup .popup-content h2 {
        font-weight: bold;
    }

    #finalPopup .popup-content h3 {
        font-weight: normal;
    }

   
   .popup-content img {
        width: 100%;
        max-width: 250px;
        height: 150px;
        border-radius: 10px;
        /* margin-top: 15px; */
    }

.gift{
    object-fit: contain;
}

.nextTime{
    font-size: 25px;
    font-weight: bolder;

}
</style>


@endpush


@section('content')
<div class="scratch-page">
    <div id="sprinkleOverlay" class="sprinkle">
        <canvas id="confettiCanvas"></canvas>
    </div>

    <div class="scratch-container">
        @if($already_scratched && $gift)
        <div id="finalPopup" class="fullscreen-popup" >
            <div class="popup-content">
                <h2>Congratulations, {{ $employee->name }}!</h2>
                <h3>You've won: {{ $gift->name }}</h3>
                    <img src="{{ asset($gift->media) }}" alt="{{ $gift->name }}" class="gift"/>
            </div>

        @elseif($already_scratched && $gift == null)
            <div id="finalPopup" class="fullscreen-popup" >
            <div class="popup-content">
                <h3>Whoops! Better Luck Next Time</h3>
                    <img src="{{ asset('images/cong.jpg') }}" alt="Better luck next time" />
            </div>
        
        @else
                  
            <h2 class="title">Scratch Card</h2>
            <div id="scratch-area" style="background-color: white; border-radius: 12px; overflow: hidden;">
                @if($gift && $gift->media)
                    <img id="giftImage" src="{{ asset($gift->media) }}" alt="Gift" />
                @else
                    <p class="nextTime">Whoops! Better Luck Next Time</p>
                @endif
                <canvas id="scratchCanvas" width="500" height="300"></canvas>
            </div>

            <!-- Fullscreen Popup Div (Initially Hidden) -->
            {{-- <div id="finalPopup" class="fullscreen-popup" style="display: none;">
                    <div class="popup-content" id="popupContent">
                        @if($gift)
                            <h2>Congratulations, {{ $employee->name }}!</h2>
                            <h3>You won: {{ $gift->name }}</h3>
                            
                                <img src="{{ asset($gift->media) }}" alt="{{ $gift->name }}">
                            
                        @else
                            <h2>Hello, {{ $employee->name }}!</h2>
                            <h3 class="nextTime">Better Luck Next Time</h3>
                                <img src="{{ asset('images/cong.jpg') }}" alt="Better luck next time" />
                        @endif
                    </div>
                </div>--}}

        @endif
    </div>
</div>
@endsection
