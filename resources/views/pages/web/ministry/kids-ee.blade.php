@push('css')
    <style>
        /* ========================= Micro-animações ========================= */
        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .7s ease, transform .7s ease;
            will-change: opacity, transform;
        }

        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .floaty {
            animation: floaty 6s ease-in-out infinite;
            will-change: transform;
        }

        @keyframes floaty {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .wiggle {
            animation: wiggle 2.5s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes wiggle {

            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(3.2deg);
            }
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /* ========================= Reveal on scroll ========================= */
            const revealEls = document.querySelectorAll('.reveal');
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-visible');
                        io.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.12
            });
            revealEls.forEach(el => io.observe(el));
        });
    </script>
@endpush

@php
    $title = __('EE-Kids');
    $description =
        'Evangelismo e discipulado para crianças com o programa Esperança Para Crianças (EPC), usando aprendizado ativo, histórias, jogos e prática supervisionada.';
    $keywords =
        'evangelismo para crianças, EE-Kids, Esperança Para Crianças, EPC, ministério infantil, discipulado infantil';
    $ogImage = asset('images/certificate-hope-for-kids-workshop.webp');

@endphp

<x-layouts.guest>
    <div>
        <x-web.header :title="$title" subtitle='Equipando crianças para levar esperança ao mundo' :cover="asset('images/ee-kids/child.png')" />

        <x-web.kids-ee.hero />
        <x-web.kids-ee.about />
        <x-web.kids-ee.methodology />
        <x-web.kids-ee.trainings />
        <x-web.kids-ee.list-events />
        <x-web.kids-ee.faq />
    </div>

</x-layouts.guest>
