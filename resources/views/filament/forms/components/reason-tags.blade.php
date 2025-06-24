<div>
    @if(count($tags) > 0)
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($tags as $tag)
                <button 
                    type="button" 
                    x-data="{}"
                    x-on:click="
                        $dispatch('tag-clicked', { tag: '{{ $tag }}' });
                    "
                    class="px-3 py-1 text-xs font-medium rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors cursor-pointer"
                >
                    {{ $tag }}
                </button>
            @endforeach
        </div>
    @endif

    <script>
        document.addEventListener('tag-clicked', function(event) {
            const tag = event.detail.tag;
            const livewireId = '{{ $livewire->getId() }}';
            const livewireComponent = window.Livewire.find(livewireId);
            
            if (livewireComponent) {
                const currentReason = livewireComponent.get('data.reason') || '';
                const newReason = currentReason ? currentReason + ' ' + tag : tag;
                livewireComponent.set('data.reason', newReason);
            }
        });
    </script>
</div>