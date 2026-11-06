@auth
    @php
        $current = auth()->user()->currentWorkspace();
        $all = auth()->user()->workspaces;
    @endphp

    @if($current)
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 text-sm rounded-md hover:bg-gray-100">
                <span>{{ $current->name }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false"
                 class="absolute left-0 mt-1 w-56 bg-white border rounded-md shadow-lg z-50">
                @foreach($all as $workspace)
                    <form method="POST" action="{{ route('dashboard.workspace.switch') }}">
                        @csrf
                        <input type="hidden" name="workspace_id" value="{{ $workspace->id }}">
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 {{ $workspace->id === $current->id ? 'font-semibold' : '' }}">
                            {{ $workspace->name }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif
@endauth
