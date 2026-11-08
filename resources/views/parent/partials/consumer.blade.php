{{-- 1Bill consumer number with copy button. Expects $number. --}}
<div>
    <span class="pp-slip-label">1Bill consumer number</span>
    <div class="pp-consumer">
        <span class="pp-consumer-num">{{ $number }}</span>
        <button type="button" class="pp-copy" data-copy="{{ $number }}" aria-label="Copy consumer number {{ $number }}">
            <i class="fa-regular fa-copy" aria-hidden="true"></i><span>Copy</span>
        </button>
    </div>
</div>
