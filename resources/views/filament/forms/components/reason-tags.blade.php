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
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded event fired - Setting up tag click listener');
            
            // Register the event listener for tag clicks
            document.addEventListener('tag-clicked', function(event) {
                const tag = event.detail.tag;
                console.log('Tag clicked event received for tag:', tag);
                
                try {
                    // Find all textareas on the page
                    const textareas = document.querySelectorAll('textarea');
                    console.log(`Found ${textareas.length} textareas on the page`);
                    
                    // Find the textarea with label 'Description' or name containing 'reason'
                    let reasonTextarea = null;
                    
                    // First, try to find by label
                    const labels = document.querySelectorAll('label');
                    for (const label of labels) {
                        if (label.textContent.includes('Description')) {
                            // Find the closest form field
                            const fieldContainer = label.closest('.filament-forms-field-wrapper');
                            if (fieldContainer) {
                                const textarea = fieldContainer.querySelector('textarea');
                                if (textarea) {
                                    reasonTextarea = textarea;
                                    console.log('Found textarea by Description label:', textarea);
                                    break;
                                }
                            }
                        }
                    }
                    
                    // If not found by label, try by name or id
                    if (!reasonTextarea) {
                        for (const textarea of textareas) {
                            const name = textarea.getAttribute('name');
                            const id = textarea.getAttribute('id');
                            
                            if ((name && name.includes('reason')) || (id && id.includes('reason'))) {
                                reasonTextarea = textarea;
                                console.log('Found textarea by name/id:', textarea);
                                break;
                            }
                        }
                    }
                    
                    // If still not found, use the first textarea as a last resort
                    if (!reasonTextarea && textareas.length > 0) {
                        reasonTextarea = textareas[0];
                        console.log('Using first textarea as fallback:', reasonTextarea);
                    }
                    
                    if (reasonTextarea) {
                        console.log('Found textarea element:', reasonTextarea);
                        console.log('Current value:', reasonTextarea.value);
                        
                        // Get current value
                        const currentValue = reasonTextarea.value || '';
                        
                        // Append the tag
                        const newValue = currentValue ? currentValue + ' ' + tag : tag;
                        console.log('Setting new value:', newValue);
                        
                        // Set the new value directly
                        reasonTextarea.value = newValue;
                        
                        // Dispatch input and change events to ensure Livewire picks up the change
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
                } catch (error) {
                    console.error('Error handling tag click:', error);
                }
            });
        });
    </script>
</div>