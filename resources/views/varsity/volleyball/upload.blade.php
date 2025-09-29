@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('varsity.dashboard') }}" class="btn btn-sm btn-outline-light me-3"><i class="bi bi-arrow-left"></i></a>
                <h2 class="text-white fw-bold mb-0">Upload Volleyball Drill</h2>
            </div>

            <!-- Status Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Upload Card -->
            <div class="card bg-dark border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('volleyball.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <div class="mb-4">
                            <label for="video" class="form-label text-white-50">Select Video File</label>
                            <input class="form-control form-control-lg bg-light-dark border-secondary text-white" type="file" id="video" name="video" accept="video/mp4,video/quicktime" required>
                            <div class="form-text text-secondary">Upload an MP4 or MOV file (max 50MB).</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg btn-glow" id="submitBtn">
                                <i class="bi bi-upload me-2"></i>Upload and Analyze
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="volleyball-animation-container mb-4">
            <video autoplay loop muted playsinline class="volleyball-animation">
                <source src="{{ asset('videos/volley.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <p class="text-white mt-3 fw-bold" id="loadingMessage">Analyzing your video... please wait.</p>
        <p class="text-white-50 mt-2" id="triviaMessage"></p>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <a href="{{ route('varsity.dashboard') }}" class="btn btn-outline-light mt-4">Back to Dashboard</a>
        <p class="text-white-50 mt-2">You can safely navigate away; processing will continue in the background.</p>
    </div>
</div>

@push('styles')
<style>
.bg-light-dark {
    background-color: #2c3a4e !important;
}
.btn-glow {
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    transition: all 0.3s ease;
}
.btn-glow:hover {
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.8);
}
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(16, 23, 42, 0.9);
    backdrop-filter: blur(8px);
    display: none; /* Hidden by default */
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 9999;
}

.volleyball-animation-container {
    width: 250px; /* Increased size */
    height: 250px; /* Increased size */
    border-radius: 50%; /* Makes it circular */
    overflow: hidden; /* Clips content to the circle */
    border: 2px solid rgba(255, 255, 255, 0.1); /* Thinner and more transparent border */
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 0 15px rgba(0, 123, 255, 0.3); /* Softer shadow */
}

.volleyball-animation {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the video fills the circular container */
}

.loading-overlay p {
    font-size: 1.2rem;
    text-align: center;
    margin-top: 1rem;
}

.loading-overlay .spinner-border {
    width: 3rem;
    height: 3rem;
    margin-top: 1.5rem;
}

@keyframes highlightFade {
    0% { opacity: 0.5; transform: scale(0.95); }
    50% { opacity: 1; transform: scale(1); }
    100% { opacity: 0.5; transform: scale(0.95); }
}

.highlight-trivia {
    animation: highlightFade 3s ease-in-out infinite;
    color: #fff !important; /* Ensure it's white when highlighted */
    text-shadow: 0 0 8px rgba(0, 123, 255, 0.7); /* Subtle glow */
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingMessage = document.getElementById('loadingMessage');
    const messages = [
        "Analyzing your drill... this might take a few moments.",
        "Performing pose estimation...",
        "Analyzing action metrics...",
        "Generating AI feedback...",
        "Almost there!"
    ];
    let messageIndex = 0;

    const triviaMessages = [
        "Did you know? Volleyball was invented in 1895 by William G. Morgan, originally called 'Mintonette'.",
        "The longest volleyball match ever recorded lasted 75 hours and 30 minutes!",
        "A volleyball can be served at speeds over 80 mph (130 km/h)!",
        "Beach volleyball became an Olympic sport in 1996, nearly 30 years after indoor volleyball.",
        "The first volleyball was actually a basketball bladder.",
        "The game of volleyball was originally designed for older members of the YMCA who found basketball too strenuous.",
        "The first official game of volleyball was played in 1896 at Springfield College.",
        "The net height for men's volleyball is 2.43 meters (7 feet 11 5/8 inches).",
        "The net height for women's volleyball is 2.24 meters (7 feet 4 1/8 inches).",
        "A standard volleyball court is 18 meters (59 feet) long and 9 meters (29.5 feet) wide.",
        "The first two-person beach volleyball game was played in 1930 in Santa Monica, California.",
        "The FIVB (Fédération Internationale de Volleyball) was founded in 1947.",
        "Volleyball was first introduced to the Olympic Games in Tokyo in 1964.",
        "The record for the most Olympic gold medals in indoor volleyball is held by the Soviet Union (men) and Cuba (women).",
        "The term 'volleyball' was coined by Alfred Halstead, after observing the 'volleying' nature of the game.",
        "The first special ball for volleyball was designed in 1900.",
        "The game was initially played with a minimum of three hits per side, but this rule was later changed.",
        "The fastest recorded spike in men's volleyball was by Ivan Zaytsev at 134 km/h (83 mph).",
        "The fastest recorded spike in women's volleyball was by Yanelis Santos at 103 km/h (64 mph).",
        "A player can hit the ball twice in a row only if the first hit was a block.",
        "The libero player wears a different colored jersey and specializes in defense and passing.",
        "The libero cannot serve, block, or attack the ball if it is entirely above the height of the net.",
        "Rally scoring, where a point is scored on every rally, was introduced in 1999.",
        "Before rally scoring, only the serving team could score a point.",
        "The longest rally in professional volleyball can last over a minute.",
        "The highest vertical jump recorded in volleyball is over 4 feet.",
        "Volleyball is played by over 800 million people worldwide, making it one of the most popular sports.",
        "The first World Championship for men was held in 1949, and for women in 1952.",
        "The game of volleyball requires excellent hand-eye coordination and agility.",
        "A 'dig' is a defensive play where a player saves a hard-driven attack.",
        "A 'set' is an overhead pass used to position the ball for a hitter.",
        "A 'block' is a defensive play at the net to stop an opponent's attack.",
        "A 'spike' or 'attack' is an offensive play where a player hits the ball forcefully over the net.",
        "The 'antennae' on the net are 80 cm long and extend 1 meter above the net.",
        "The ball must be hit cleanly; no catching, throwing, or holding is allowed.",
        "A 'pancake' is a defensive technique where a player extends their hand flat on the floor to save the ball.",
        "The 'rotation' rule ensures all players get a chance to play in different positions.",
        "There are six players on each side in indoor volleyball.",
        "Beach volleyball typically has two players per side.",
        "The 'deciding set' in indoor volleyball is usually played to 15 points, with a minimum two-point lead.",
        "The 'foot fault' occurs when a server steps on or over the end line before contacting the ball.",
        "The 'double hit' is an illegal contact where a player touches the ball twice in immediate succession.",
        "The 'lift' or 'carry' is an illegal contact where the ball is held or thrown.",
        "Volleyball was originally played with 9 players per side.",
        "The 'back row attack' rule prevents back-row players from attacking the ball from in front of the 3-meter line.",
        "The 'libero' position was introduced in 1998 to enhance defensive play.",
        "The 'jump serve' is a powerful serve where the player tosses the ball and jumps before hitting it.",
        "The 'float serve' is a serve with no spin, making its trajectory unpredictable.",
        "The 'cut shot' in beach volleyball is an attack hit sharply across the court.",
        "The 'line shot' in beach volleyball is an attack hit straight down the sideline.",
        "The 'wipe' or 'tool' is an attack where the hitter intentionally hits the ball off the opponent's block out of bounds.",
        "The 'read block' is a blocking strategy where the blocker waits to see the hitter's approach before committing.",
        "The 'commit block' is a blocking strategy where the blocker commits to a specific area before the hitter attacks.",
        "The 'pipe attack' is a back-row attack hit from the middle of the court.",
        "The 'setter dump' is a surprise attack by the setter, tipping the ball over the net.",
        "The 'six-pack' is a slang term for when a spiked ball hits a player directly in the face or chest.",
        "The 'roof' is a slang term for a block that completely stops an opponent's attack.",
        "The 'digging machine' is a slang term for a player who is exceptionally good at digging.",
        "The 'kill' is a successful attack that results in an immediate point or side out.",
        "The 'ace' is a serve that results directly in a point without the opponent being able to return it.",
        "The 'stuff block' is a block that immediately sends the ball back to the opponent's side for a point.",
        "The 'transition' in volleyball refers to the movement from defense to offense or vice versa.",
        "The 'antennae' are considered extensions of the net and any ball touching them is out.",
        "The 'attack line' is 3 meters (9 feet 10 inches) from the net.",
        "The 'service zone' is 9 meters (29.5 feet) wide.",
        "The 'back court' is the area behind the attack line.",
        "The 'front court' is the area between the net and the attack line.",
        "The 'center line' is directly under the net.",
        "A player cannot touch the net during play.",
        "A player cannot cross the center line under the net if it interferes with play.",
        "The ball can touch the net on a serve and still be in play, as long as it goes over.",
        "A team has three touches to return the ball over the net.",
        "A block does not count as one of the three touches.",
        "The ball can be hit with any part of the body, as long as it's a clean contact.",
        "The 'technical timeout' is taken when the leading team reaches 8 and 16 points in sets 1-4.",
        "There are two 30-second timeouts allowed per team per set.",
        "Substitutions are allowed during dead ball situations.",
        "A team can make a maximum of 6 substitutions per set.",
        "The 'coach's challenge' allows coaches to challenge a referee's decision.",
        "The 'video challenge system' is used in professional volleyball to review plays.",
        "The 'Mintonette' name was changed to 'Volley Ball' in 1896.",
        "The game was initially played with no limit to the number of players.",
        "The first international volleyball federation was established in Paris.",
        "Volleyball is a non-contact sport.",
        "The 'setter' is often considered the 'quarterback' of the team.",
        "The 'outside hitter' attacks from the left front position.",
        "The 'opposite hitter' attacks from the right front position.",
        "The 'middle blocker' specializes in blocking and quick attacks.",
        "The 'defensive specialist' focuses on passing and digging.",
        "The 'service ace' is a serve that lands in bounds without being touched by the opponent.",
        "The 'service error' is a serve that goes out of bounds or into the net.",
        "The 'attack error' is an attack that goes out of bounds or into the net.",
        "The 'blocking error' is a block that results in a point for the opponent.",
        "The 'dig error' is a dig that is not successfully passed to a teammate.",
        "The 'setting error' is a set that is not accurately placed for a hitter.",
        "The 'reception error' is a serve receive that is not successfully passed to the setter.",
        "The 'free ball' is a ball returned by the opponent that is not an attack, allowing the team to set up an offense.",
        "The 'down ball' is a soft attack hit downwards, often used when a powerful spike isn't possible.",
        "The 'roll shot' is a soft attack hit with topspin, causing it to drop quickly.",
        "The 'tip' or 'dink' is a soft attack hit over the block.",
        "The 'cross-court shot' is an attack hit diagonally across the court.",
        "The 'line shot' is an attack hit straight down the sideline.",
        "The 'seam' is the area between two blockers.",
        "The 'tooling the block' is intentionally hitting the ball off the block out of bounds.",
        "The 'transition play' is the quick change from defense to offense.",
        "The 'serve receive' is the first contact made by the receiving team.",
        "The 'pass' is the act of receiving a serve or attack.",
        "The 'bump' is a forearm pass.",
        "The 'overhead pass' is a pass made with open hands above the head.",
        "The 'jump set' is a set made while jumping.",
        "The 'back set' is a set made behind the setter's head.",
        "The 'quick attack' is a fast attack often used by middle blockers.",
        "The 'slide attack' is an attack where the hitter runs parallel to the net before jumping.",
        "The 'combination play' involves multiple attackers to confuse the block.",
        "The 'decoy' is a player who pretends to attack to draw the block away.",
        "The 'read defense' is a defensive strategy where players react to the hitter's attack.",
        "The 'perimeter defense' is a defensive strategy where players cover the boundaries of the court.",
        "The 'rotational defense' is a defensive strategy where players rotate to cover different areas.",
        "The 'dig-set-hit' is the fundamental sequence of plays in volleyball.",
        "The 'triple block' involves three players blocking at the net.",
        "The 'double block' involves two players blocking at the net.",
        "The 'single block' involves one player blocking at the net.",
        "The 'off-blocker' is a blocker who is not directly in front of the hitter.",
        "The 'cover' is when players position themselves to retrieve a blocked ball.",
        "The 'out-of-system' play occurs when the first pass is not perfect, making it harder to set up an attack.",
        "The 'in-system' play occurs when the first pass is perfect, allowing for a well-executed offense.",
        "The 'tempo' of an offense refers to the speed and timing of the sets and attacks.",
        "The 'court awareness' is a player's understanding of their position and the positions of others on the court.",
        "The 'game plan' is the strategy a team uses to win a match.",
        "The 'momentum' in volleyball can shift quickly between teams.",
        "The 'mental game' is as important as the physical game in volleyball.",
        "The 'team chemistry' is crucial for success in volleyball.",
        "The 'communication' between players is vital for effective teamwork.",
        "The 'leadership' on the court can inspire a team to victory.",
        "The 'sportsmanship' in volleyball promotes fair play and respect.",
        "The 'passion' for the game drives players to excel.",
        "The 'dedication' to practice leads to improvement.",
        "The 'resilience' helps players overcome challenges.",
        "The 'focus' during a match is key to performance.",
        "The 'discipline' in executing plays is essential.",
        "The 'adaptability' to different situations is important.",
        "The 'creativity' in attacking and defending makes the game exciting.",
        "The 'joy' of playing volleyball is what keeps players coming back.",
        "The 'community' of volleyball is supportive and welcoming.",
        "The 'spirit' of volleyball is about teamwork and fun.",
        "The 'history' of volleyball is rich and evolving.",
        "The 'future' of volleyball is bright with new innovations.",
        "The 'global reach' of volleyball makes it a truly international sport.",
        "The 'health benefits' of playing volleyball include improved cardiovascular health and strength.",
        "The 'social benefits' of playing volleyball include building friendships and teamwork skills.",
        "The 'strategic elements' of volleyball make it a thinking person's game.",
        "The 'physical demands' of volleyball require agility, strength, and endurance.",
        "The 'mental demands' of volleyball require quick decision-making and focus.",
        "The 'evolution' of volleyball rules has made the game faster and more exciting.",
        "The 'impact' of technology on volleyball includes video analysis and performance tracking.",
        "The 'role of the coach' in volleyball is to guide, motivate, and strategize.",
        "The 'role of the referee' in volleyball is to ensure fair play and enforce rules.",
        "The 'role of the fans' in volleyball is to support and cheer for their teams.",
        "The 'atmosphere' at a volleyball match can be electrifying.",
        "The 'sound' of a powerful spike is unmistakable.",
        "The 'feeling' of a perfect pass is incredibly satisfying.",
        "The 'excitement' of a long rally keeps everyone on the edge of their seats.",
        "The 'drama' of a close match is unforgettable.",
        "The 'beauty' of a well-executed play is a joy to behold.",
        "The 'challenge' of mastering volleyball skills is rewarding.",
        "The 'satisfaction' of winning a tough match is immense.",
        "The 'lessons learned' from volleyball extend beyond the court.",
        "The 'memories made' playing volleyball last a lifetime.",
        "The 'friendships forged' through volleyball are strong and enduring.",
        "The 'passion shared' for volleyball unites players worldwide.",
        "The 'love for the game' is what truly defines a volleyball player.",
        "The 'Olympic motto' Citius, Altius, Fortius (Faster, Higher, Stronger) perfectly describes volleyball.",
        "The 'spirit of the game' in volleyball emphasizes fair play and respect for opponents.",
        "The 'volleyball net' is typically made of nylon or similar synthetic material.",
        "The 'volleyball ball' is usually made of leather or synthetic leather.",
        "The 'antennae' are striped red and white.",
        "The 'court lines' are 5 cm (2 inches) wide.",
        "The 'attack line' is also known as the 10-foot line in some regions.",
        "The 'service line' is the end line of the court.",
        "The 'sidelines' mark the outer boundaries of the court.",
        "The 'end lines' mark the back boundaries of the court.",
        "The 'substitution zone' is between the attack line and the center line.",
        "The 'warm-up area' is typically outside the court boundaries.",
        "The 'coaching zone' is the area where coaches can stand during play.",
        "The 'referee stand' is positioned at one end of the net.",
        "The 'scorekeeper' records points and substitutions.",
        "The 'line judges' assist the referee in calling balls in or out.",
        "The 'ball retrievers' ensure the game flows smoothly by retrieving balls.",
        "The 'team bench' is where non-playing team members sit.",
        "The 'medical staff' is present to attend to injuries.",
        "The 'media area' is designated for journalists and photographers.",
        "The 'spectator area' is where fans watch the game.",
        "The 'atmosphere' of a volleyball game is often energetic and loud.",
        "The 'sound of the whistle' signals the start or end of a rally.",
        "The 'cheers from the crowd' add to the excitement of the game.",
        "The 'team huddle' is a moment for players to strategize and motivate each other.",
        "The 'high five' is a common gesture of celebration and encouragement.",
        "The 'fist bump' is another common gesture of camaraderie.",
        "The 'team chant' can boost morale and intimidate opponents.",
        "The 'victory celebration' is a moment of joy and accomplishment.",
        "The 'post-game handshake' shows respect between teams.",
        "The 'MVP award' recognizes the most valuable player.",
        "The 'All-Star team' comprises the best players in a league or tournament.",
        "The 'Hall of Fame' honors legendary volleyball players and coaches.",
        "The 'youth volleyball' programs introduce the sport to young players.",
        "The 'collegiate volleyball' scene is highly competitive in many countries.",
        "The 'professional volleyball' leagues showcase the highest level of play.",
        "The 'international competitions' like the Olympics and World Championships are major events.",
        "The 'volleyball community' is a global network of players, coaches, and fans.",
        "The 'growth of volleyball' continues worldwide.",
        "The 'popularity of beach volleyball' has soared in recent decades.",
        "The 'indoor volleyball' remains a classic and beloved sport.",
        "The 'sitting volleyball' is an adapted version for athletes with disabilities.",
        "The 'snow volleyball' is a new and exciting variation of the sport.",
        "The 'grass volleyball' is a casual and fun way to play.",
        "The 'mini volleyball' is designed for younger players.",
        "The 'volleyball drills' help players improve their skills.",
        "The 'volleyball practice' is essential for team development.",
        "The 'volleyball match' is the ultimate test of skill and teamwork.",
        "The 'volleyball tournament' brings together teams for competition.",
        "The 'volleyball season' is a period of intense training and matches.",
        "The 'off-season' is a time for rest and individual improvement.",
        "The 'pre-season' is a time for conditioning and team building.",
        "The 'playoffs' determine the champions of a league.",
        "The 'championship game' is the culmination of a season.",
        "The 'gold medal match' is the most anticipated game in the Olympics.",
        "The 'bronze medal match' determines the third-place finisher.",
        "The 'opening ceremony' of a tournament marks its beginning.",
        "The 'closing ceremony' celebrates the end of a tournament.",
        "The 'awards ceremony' recognizes the achievements of teams and players.",
        "The 'national anthem' is played before international matches.",
        "The 'team uniform' identifies players and their team.",
        "The 'knee pads' protect players during dives and falls.",
        "The 'ankle braces' provide support and prevent injuries.",
        "The 'volleyball shoes' are designed for quick movements and jumps.",
        "The 'water bottle' is essential for hydration during play.",
        "The 'towel' is used to wipe away sweat.",
        "The 'first aid kit' is important for treating minor injuries.",
        "The 'whistle' is used by the referee to control the game.",
        "The 'score sheet' is used to record game statistics.",
        "The 'clipboard' is used by coaches for strategy and notes.",
        "The 'stopwatch' is used to time timeouts and intervals.",
        "The 'net antenna' helps determine if a ball is in or out.",
        "The 'court boundaries' define the playing area.",
        "The 'attack zone' is the area where front-row players can attack.",
        "The 'back row zone' is the area where back-row players play defense.",
        "The 'service zone' is where the server stands.",
        "The 'substitution zone' is where players enter and exit the court.",
        "The 'warm-up area' is where players prepare before a match.",
        "The 'cool-down area' is where players recover after a match.",
        "The 'locker room' is where players change and store their belongings.",
        "The 'training facility' is where teams practice and train.",
        "The 'weight room' is used for strength and conditioning.",
        "The 'therapy room' is used for injury treatment and prevention.",
        "The 'meeting room' is used for team discussions and video analysis.",
        "The 'dining area' is where teams eat meals.",
        "The 'team bus' transports players to and from matches.",
        "The 'hotel' provides accommodation for teams during tournaments.",
        "The 'airport' is where teams travel for international competitions.",
        "The 'passport' is required for international travel.",
        "The 'visa' may be required for entry into certain countries.",
        "The 'currency exchange' is necessary for international travel.",
        "The 'local culture' can be experienced during international tournaments.",
        "The 'fan support' is a huge motivator for players.",
        "The 'home court advantage' can make a big difference in a match.",
        "The 'away game' presents unique challenges for teams.",
        "The 'neutral site game' is played at a location not home to either team.",
        "The 'rivalry game' is a highly anticipated match between two competing teams.",
        "The 'derby match' is a game between two local rival teams.",
        "The 'classic match' is a memorable game from the past.",
        "The 'upset victory' is when an underdog team wins against a favored opponent.",
        "The 'comeback win' is when a team overcomes a large deficit to win.",
        "The 'close game' is a match with a small point difference.",
        "The 'blowout game' is a match with a large point difference.",
        "The 'five-set thriller' is a match that goes to the maximum number of sets.",
        "The 'straight-sets victory' is when a team wins without losing a set.",
        "The 'sweep' is when a team wins all sets in a match.",
        "The 'reverse sweep' is when a team comes back from a 0-2 deficit to win 3-2.",
        "The 'match point' is the point that can win the match.",
        "The 'set point' is the point that can win the set.",
        "The 'game point' is another term for set point.",
        "The 'break point' is when the receiving team scores a point on the serving team.",
        "The 'side out' is when the receiving team wins the rally and gains the serve.",
        "The 'service winner' is a serve that is difficult to return but is touched by the opponent.",
        "The 'service ace' is a serve that is not touched by the opponent.",
        "The 'service error' is a serve that goes out of bounds or into the net.",
        "The 'net serve' is a serve that touches the net but still goes over.",
        "The 'let serve' is a serve that touches the net but still goes over (same as net serve)."]
    let triviaIndex = 0;

    function updateLoadingMessage() {
        loadingMessage.textContent = messages[messageIndex];
        messageIndex = (messageIndex + 1) % messages.length;
    }

    function updateTriviaMessage() {
        triviaMessage.classList.remove('highlight-trivia');
        // Trigger reflow to restart animation
        void triviaMessage.offsetWidth;
        triviaMessage.textContent = triviaMessages[triviaIndex];
        triviaMessage.classList.add('highlight-trivia');
        triviaIndex = (triviaIndex + 1) % triviaMessages.length;
    }

    uploadForm.addEventListener('submit', function() {
        const videoInput = document.getElementById('video');
        if (videoInput.files.length > 0) {
            loadingOverlay.style.display = 'flex';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Analyzing...';
            updateLoadingMessage(); // Display initial message
            setInterval(updateLoadingMessage, 5000); // Change message every 5 seconds
            updateTriviaMessage(); // Display initial trivia
            setInterval(updateTriviaMessage, 15000); // Change trivia every 15 seconds
        }
    });

    function updateLoadingMessage() {
        loadingMessage.textContent = messages[messageIndex];
        messageIndex = (messageIndex + 1) % messages.length;
    }

    function updateTriviaMessage() {
        triviaMessage.classList.remove('highlight-trivia');
        // Trigger reflow to restart animation
        void triviaMessage.offsetWidth;
        triviaMessage.textContent = triviaMessages[triviaIndex];
        triviaMessage.classList.add('highlight-trivia');
        triviaIndex = (triviaIndex + 1) % triviaMessages.length;
    }

    uploadForm.addEventListener('submit', function() {
        const videoInput = document.getElementById('video');
        if (videoInput.files.length > 0) {
            loadingOverlay.style.display = 'flex';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Analyzing...';
            updateLoadingMessage(); // Display initial message
            setInterval(updateLoadingMessage, 5000); // Change message every 5 seconds
            updateTriviaMessage(); // Display initial trivia
            setInterval(updateTriviaMessage, 7000); // Change trivia every 7 seconds
        }
    });
});
</script>
@endpush

@endsection