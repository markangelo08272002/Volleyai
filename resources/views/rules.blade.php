<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules and Regulations</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold text-center mb-8">Volleyball Rules and Regulations</h1>

        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-bold mb-4">Basic Rules</h2>
            <ul class="list-disc list-inside">
                <li>Each team is allowed a maximum of three touches before hitting the ball over the net.</li>
                <li>A player cannot hit the ball twice in a row.</li>
                <li>The ball can be played off the net during a volley and on a serve.</li>
                <li>A ball is in if it hits any part of the court, including the boundary lines.</li>
                <li>A ball is out if it hits an antenna, the floor completely outside the court, any of the net or cables outside the antennae, the referee stand or pole, or the ceiling.</li>
                <li>It is legal to contact the ball with any part of a player’s body.</li>
                <li>It is illegal to catch, hold, or throw the ball.</li>
            </ul>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-bold mb-4">AI Demo Action</h2>
            <div class="flex justify-center">
                <video controls src="/videos/volley.mp4" class="w-full md:w-1/2"></video>
            </div>
            <div id="voice-explanation" class="mt-4 text-center">
                <p>This is a demonstration of a proper volleyball spike. The AI will now provide a voice explanation of the key movements.</p>
                <button id="play-voice" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">Play Voice Explanation</button>
            </div>
        </div>

        <div class="text-center">
            <form action="{{ route('rules.accept') }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">I have read and understood the rules</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('play-voice').addEventListener('click', () => {
            const text = document.getElementById('voice-explanation').querySelector('p').textContent;
            const utterance = new SpeechSynthesisUtterance(text);
            speechSynthesis.speak(utterance);
        });
    </script>
</body>
</html>
