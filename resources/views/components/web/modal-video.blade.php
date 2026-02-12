<div id="videoModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" data-close-modal></div>

    <div class="relative mx-auto flex min-h-screen max-w-4xl items-center px-4">
        <div class="relative w-full overflow-hidden rounded-2xl bg-black shadow-2xl">
            <button type="button"
                class="absolute right-3 top-3 z-10 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-slate-900 ring-1 ring-black/10 hover:bg-white"
                data-close-modal aria-label="Fechar vídeo">
                Fechar ✕
            </button>

            <div class="aspect-video w-full">
                <iframe id="videoFrame" class="h-full w-full" src="" title="Vídeo do YouTube" frameborder="0"
                    allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>

@once
    @push('js')
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const modal = document.getElementById("videoModal");
                const frame = document.getElementById("videoFrame");
                if (!modal || !frame) return;

                function openVideoModal(videoId) {
                    frame.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&rel=0`;
                    modal.classList.remove("hidden");
                    document.body.style.overflow = "hidden";
                }

                function closeVideoModal() {
                    modal.classList.add("hidden");
                    frame.src = "";
                    document.body.style.overflow = "";
                }

                // Delegação: serve para qualquer .js-video-btn em qualquer página
                document.addEventListener("click", (e) => {
                    const btn = e.target.closest(".js-video-btn");
                    if (btn) {
                        const id = btn.dataset.videoId;
                        if (id) openVideoModal(id);
                        return;
                    }

                    if (e.target.closest("[data-close-modal]")) closeVideoModal();
                });

                document.addEventListener("keydown", (e) => {
                    if (e.key === "Escape") closeVideoModal();
                });
            });
        </script>
    @endpush
@endonce
