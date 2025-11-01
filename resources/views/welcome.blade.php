@extends('layouts.main')

@section('content')
<div class="text-center pt-4 pb-5">
    <h1 class="section-title display-4 mb-3">Elevate Your Game with AI Analysis</h1>
    <p class="section-subtitle mb-5">
        Leverage cutting-edge pose estimation to analyze volleyball skills, get instant, actionable feedback, and track your performance over time.
    </p>
    <a href="{{ route('login') }}" class="btn btn-glow px-5 py-3 fs-5">Get Started Now</a>
</div>

<div class="row g-4 mb-5 pb-5">
    <div class="col-lg-4 col-md-6">
        <div class="feature-card">
            <i class="bi bi-camera-reels"></i>
            <h5 class="card-title mt-3">Seamless Video Analysis</h5>
            <p class="card-text">Upload your game or drill footage and let our AI provide a frame-by-frame analysis of your technique.</p>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="feature-card">
            <i class="bi bi-graph-up-arrow"></i>
            <h5 class="card-title mt-3">Objective Performance Scoring</h5>
            <p class="card-text">Receive an unbiased score from 0-100 based on form accuracy, timing, and movement efficiency.</p>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="feature-card">
            <i class="bi bi-lightbulb"></i>
            <h5 class="card-title mt-3">Intelligent AI Feedback</h5>
            <p class="card-text">Get personalized suggestions with visual overlays to understand exactly how to improve your skills.</p>
        </div>
    </div>
</div>

<div class="text-center mb-5">
    <h2 class="section-title mb-3">Built for Varsity Players and Coaches</h2>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="role-card">
            <i class="bi bi-trophy"></i>
            <h6 class="card-title">Varsity Player</h6>
            <ul class="list-unstyled role-list mt-3 small text-start ps-4">
                <li>› Advanced Motion Analytics</li>
                <li>› In-depth Movement Analysis</li>
                <li>› Visual Improvement Guides</li>
                <li>› Technical Skill Breakdowns</li>
            </ul>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="role-card">
            <i class="bi bi-diagram-3"></i>
            <h6 class="card-title">Coach</h6>
            <ul class="list-unstyled role-list mt-3 small text-start ps-4">
                <li>› Team & Player Analysis</li>
                <li>› Technical Comparisons</li>
                <li>› Advanced Coaching Tools</li>
                <li>› Actionable Performance Insights</li>
            </ul>
        </div>
    </div>
</div>
@endsection