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
        // Wait for Livewire and Alpine to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Register the event listener for tag clicks
            document.addEventListener('tag-clicked', function(event) {
                const tag = event.detail.tag;
                
                // Get the textarea element directly
                const reasonTextarea = document.querySelector('[name="reason"]');
                
                if (reasonTextarea) {
                    // Get current value
                    const currentValue = reasonTextarea.value || '';
                    
                    // Append the tag
                    const newValue = currentValue ? currentValue + ' ' + tag : tag;
                    
                    // Set the new value
                    reasonTextarea.value = newValue;
                    
                    // Dispatch input event to trigger Livewire update
                    reasonTextarea.dispatchEvent(new Event('input', { bubbles: true }));
                    reasonTextarea.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    // Focus the textarea
                    reasonTextarea.focus();
                    
                    // Set cursor at the end
                    const len = reasonTextarea.value.length;
                    reasonTextarea.setSelectionRange(len, len);
                    
                    console.log('Updated textarea with tag:', tag);
                } else {
                    console.error('Could not find reason textarea element');
                }
            });
        });
    </script>
</div>