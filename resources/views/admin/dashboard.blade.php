<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    Welcome, {{ auth()->user()->name }}! You are logged in as <strong>Admin</strong>.
                </div>
            </div>
        </div>
    </div>

@if(!session('rules_accepted'))
<div class="modal fade" id="rulesModal" tabindex="-1" aria-labelledby="rulesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="rulesModalLabel">Rules and Regulations of Volleyball</h5>
            </div>
            <div class="modal-body">
                <div id="rules-content">
                    <h6>1. The Court and Equipment</h6>
                    <ul>
                        <li>Court size: 18m long × 9m wide, divided by a net.</li>
                        <li>Net height:
                            <ul>
                                <li>Men – 2.43m</li>
                                <li>Women – 2.24m</li>
                            </ul>
                        </li>
                        <li>Ball: Round, leather or synthetic, circumference 65–67 cm, weight 260–280 g.</li>
                    </ul>

                    <h6>2. The Teams</h6>
                    <ul>
                        <li>Players: 6 players on court (3 front row, 3 back row).</li>
                        <li>Minimum: A team must have at least 6 players to start a set.</li>
                        <li>Substitutions: Up to 6 per set.</li>
                    </ul>

                    <h6>3. Scoring System</h6>
                    <ul>
                        <li>Rally Point System: Every rally scores a point.</li>
                        <li>Winning a Set: First to 25 points, must lead by 2 points.</li>
                        <li>Winning a Match: Best of 5 sets. If tied 2–2, the 5th set is played to 15 points (must lead by 2).</li>
                    </ul>

                    <h6>4. Starting and Playing the Game</h6>
                    <ul>
                        <li>Coin Toss: Decides serve or side.</li>
                        <li>Rotation: After gaining serve, players rotate clockwise.</li>
                        <li>Serving:
                            <ul>
                                <li>Must be done from behind the end line.</li>
                                <li>Ball must be hit within 8 seconds after referee’s whistle.</li>
                                <li>Only one toss attempt allowed.</li>
                            </ul>
                        </li>
                    </ul>

                    <h6>5. Playing the Ball</h6>
                    <ul>
                        <li>A team may hit the ball up to 3 times before sending it over the net.</li>
                        <li>A player cannot hit the ball twice in succession (except after a block).</li>
                        <li>Allowed hits: Bump (forearm pass), set, spike, block, dig.</li>
                        <li>Illegal hits: Lift, carry, double contact.</li>
                    </ul>

                    <h6>6. Ball In and Out</h6>
                    <ul>
                        <li>Ball in: When it lands on or inside boundary lines.</li>
                        <li>Ball out: When it touches floor outside the lines, antennas, net posts, or ceiling.</li>
                    </ul>

                    <h6>7. Net Rules</h6>
                    <ul>
                        <li>Players may not touch the net while playing the ball.</li>
                        <li>The ball may touch the net during play and on serve (let serve is allowed).</li>
                        <li>Crossing the center line is a fault if it interferes with opponent’s play.</li>
                    </ul>

                    <h6>8. Attacking and Blocking</h6>
                    <ul>
                        <li>Front-row players: Can attack and block anywhere.</li>
                        <li>Back-row players: Must jump from behind the attack line (3m line) when spiking.</li>
                        <li>Block: Does not count as one of the three team hits.</li>
                    </ul>

                    <h6>9. Player Conduct</h6>
                    <ul>
                        <li>Respect referees and opponents.</li>
                        <li>No unsportsmanlike conduct (shouting at opponents, delaying game, etc.).</li>
                        <li>Yellow card = warning; Red card = penalty point; Red + Yellow = expulsion.</li>
                    </ul>

                    <h6>10. Officials</h6>
                    <ul>
                        <li>First Referee: Stands on referee stand, controls the game.</li>
                        <li>Second Referee: Assists, monitors net faults, substitutions.</li>
                        <li>Line Judges: Signal if ball lands in/out.</li>
                        <li>Scorer: Records points, rotations, substitutions.</li>
                    </ul>
                </div>
                <div id="voice-explanation" class="mt-4 text-center">
                    <p>You can listen to the full rules and regulations.</p>
                    <button id="play-voice" class="btn btn-primary mt-2">Play Full Rules</button>
                    <button id="stop-voice" class="btn btn-danger mt-2">Stop Voice</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close-rules">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(!session('rules_accepted'))
        var rulesModal = new bootstrap.Modal(document.getElementById('rulesModal'), {
            keyboard: false,
            backdrop: 'static'
        });
        rulesModal.show();

        document.getElementById('close-rules').addEventListener('click', function() {
            fetch('/api/rules/accept', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    rulesModal.hide();
                }
            });
        });

        document.getElementById('play-voice').addEventListener('click', () => {
            const rulesText = document.getElementById('rules-content').innerText;
            const utterance = new SpeechSynthesisUtterance(rulesText);
            speechSynthesis.speak(utterance);
        });

        document.getElementById('stop-voice').addEventListener('click', () => {
            speechSynthesis.cancel();
        });
        @endif
    });
</script>
@endpush
</x-app-layout>


