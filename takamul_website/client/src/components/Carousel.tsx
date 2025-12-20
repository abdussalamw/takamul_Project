import { useEffect, useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface CarouselItem {
  id: number;
  title: string;
  description: string;
  icon: string;
}

interface CarouselProps {
  items: CarouselItem[];
  autoPlay?: boolean;
  interval?: number;
}

export default function Carousel({ items, autoPlay = true, interval = 5000 }: CarouselProps) {
  const [current, setCurrent] = useState(0);
  const [isAutoPlay, setIsAutoPlay] = useState(autoPlay);

  useEffect(() => {
    if (!isAutoPlay) return;

    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % items.length);
    }, interval);

    return () => clearInterval(timer);
  }, [isAutoPlay, items.length, interval]);

  const next = () => {
    setCurrent((prev) => (prev + 1) % items.length);
    setIsAutoPlay(false);
  };

  const prev = () => {
    setCurrent((prev) => (prev - 1 + items.length) % items.length);
    setIsAutoPlay(false);
  };

  const goToSlide = (index: number) => {
    setCurrent(index);
    setIsAutoPlay(false);
  };

  return (
    <div
      className="relative w-full"
      onMouseEnter={() => setIsAutoPlay(false)}
      onMouseLeave={() => autoPlay && setIsAutoPlay(true)}
    >
      {/* Slides Container */}
      <div className="relative overflow-hidden rounded-2xl bg-white">
        <div className="relative h-96 md:h-96">
          {items.map((item, index) => (
            <div
              key={item.id}
              className={`absolute inset-0 transition-all duration-700 ease-in-out ${
                index === current
                  ? 'opacity-100 translate-x-0'
                  : index < current
                    ? 'opacity-0 -translate-x-full'
                    : 'opacity-0 translate-x-full'
              }`}
            >
              <div className="h-full bg-gradient-to-br from-primary/10 to-secondary/10 flex items-center justify-center p-8">
                <div className="text-center space-y-6">
                  <div className="text-6xl md:text-7xl">{item.icon}</div>
                  <h3 className="text-3xl md:text-4xl font-bold text-primary">{item.title}</h3>
                  <p className="text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
                    {item.description}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Navigation Buttons */}
        <button
          onClick={prev}
          className="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-primary/20 hover:bg-primary/40 text-primary transition-all duration-300 hover:scale-110"
          aria-label="Previous slide"
        >
          <ChevronRight size={24} />
        </button>

        <button
          onClick={next}
          className="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-primary/20 hover:bg-primary/40 text-primary transition-all duration-300 hover:scale-110"
          aria-label="Next slide"
        >
          <ChevronLeft size={24} />
        </button>

        {/* Indicators */}
        <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-10">
          {items.map((_, index) => (
            <button
              key={index}
              onClick={() => goToSlide(index)}
              className={`transition-all duration-300 rounded-full ${
                index === current
                  ? 'bg-primary w-8 h-3'
                  : 'bg-primary/30 hover:bg-primary/50 w-3 h-3'
              }`}
              aria-label={`Go to slide ${index + 1}`}
            />
          ))}
        </div>
      </div>

      {/* Slide Counter */}
      <div className="text-center mt-6 text-sm text-muted-foreground">
        <span className="font-semibold text-primary">{current + 1}</span> / {items.length}
      </div>
    </div>
  );
}
