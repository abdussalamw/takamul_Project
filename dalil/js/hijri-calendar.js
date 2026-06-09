// Hijri Calendar Datepicker Script
document.addEventListener('DOMContentLoaded', function() {
    let activeCalendarInput = null;
    const calendarElement = createCalendarElement();
    document.body.appendChild(calendarElement);

    const hijriMonths = ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الثانية', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'];
    const hijriDays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    
    const hijriYearStartDay = { 1446: 0, 1447: 4, 1448: 2 };
    const hijriMonthLengths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];

    function createCalendarElement() {
        const calendar = document.createElement('div');
        calendar.className = 'hijri-calendar';
        calendar.style.cssText = `
            position: absolute; background: white; border: 1px solid #ddd; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 1002; padding: 15px;
            width: 320px; display: none; font-family: 'Tajawal', sans-serif; opacity: 0; 
            transform: translateY(10px); transition: opacity 0.3s ease, transform 0.3s ease; 
        `;
        return calendar;
    }

    function renderCalendar(year, month, selectedDay = null) {
        calendarElement.innerHTML = `
            <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <button type="button" class="nav-btn" data-action="prev-month">‹</button>
                <div style="display: flex; gap: 5px; font-weight: bold;">
                    <span id="current-month">${hijriMonths[month-1]}</span>
                    <span id="current-year">${year}هـ</span>
                </div>
                <button type="button" class="nav-btn" data-action="next-month">›</button>
            </div>
            <div class="calendar-grid-header" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 10px;">
                ${hijriDays.map(day => `<div style="text-align: center; font-weight: bold; color: var(--primary, #8a2be2); padding: 6px; font-size: 0.8rem;">${day.substring(0,3)}</div>`).join('')}
            </div>
            <div class="calendar-grid-days" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;"></div>
        `;

        const daysContainer = calendarElement.querySelector('.calendar-grid-days');
        const daysInMonth = hijriMonthLengths[month - 1] + ((month === 12 && (year === 1446 || year === 1447)) ? 1 : 0); // Simple leap year adjustment

        let firstDayOfMonth = hijriYearStartDay[year] || 0;
        for (let i = 0; i < month - 1; i++) {
            firstDayOfMonth = (firstDayOfMonth + hijriMonthLengths[i]) % 7;
        }

        for (let i = 0; i < firstDayOfMonth; i++) {
            daysContainer.innerHTML += '<div></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.textContent = day;
            dayElement.style.cssText = `text-align: center; padding: 8px 4px; cursor: pointer; border-radius: 50%; transition: all 0.2s ease; font-weight: 500;`;
            if (day === selectedDay) {
                dayElement.style.backgroundColor = 'var(--primary, #8a2be2)';
                dayElement.style.color = 'white';
            }
            dayElement.addEventListener('click', () => selectDate(year, month, day));
            dayElement.addEventListener('mouseover', () => { if(day !== selectedDay) dayElement.style.backgroundColor = '#f0e6ff'; });
            dayElement.addEventListener('mouseout', () => { if(day !== selectedDay) dayElement.style.backgroundColor = ''; });
            daysContainer.appendChild(dayElement);
        }

        calendarElement.querySelectorAll('.nav-btn').forEach(btn => {
            btn.style.cssText = `background: none; border: none; font-size: 1.5rem; color: var(--primary, #8a2be2); cursor: pointer;`;
            btn.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                let newMonth = month, newYear = year;
                if (action === 'prev-month') {
                    newMonth--;
                    if (newMonth < 1) { newMonth = 12; newYear--; }
                } else {
                    newMonth++;
                    if (newMonth > 12) { newMonth = 1; newYear++; }
                }
                renderCalendar(newYear, newMonth, selectedDay);
            });
        });
    }

    function selectDate(year, month, day) {
        if (!activeCalendarInput) return;
        const dateStr = `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`;
        activeCalendarInput.value = dateStr;
        hideCalendar();
    }

    function showCalendar(targetInput) {
        activeCalendarInput = targetInput;
        const rect = targetInput.getBoundingClientRect();
        calendarElement.style.top = `${window.scrollY + rect.bottom + 5}px`;
        calendarElement.style.right = `${window.innerWidth - rect.right}px`;

        let currentYear = 1447, currentMonth = 1, currentDay = null;
        const currentValue = targetInput.value;
        if (currentValue && /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(currentValue)) {
            const parts = currentValue.split('/');
            currentDay = parseInt(parts[0], 10);
            currentMonth = parseInt(parts[1], 10);
            currentYear = parseInt(parts[2], 10);
        }

        renderCalendar(currentYear, currentMonth, currentDay);
        calendarElement.style.display = 'block';
        setTimeout(() => {
            calendarElement.style.opacity = '1';
            calendarElement.style.transform = 'translateY(0)';
        }, 10);
    }

    function hideCalendar() {
        calendarElement.style.opacity = '0';
        calendarElement.style.transform = 'translateY(10px)';
        setTimeout(() => {
            calendarElement.style.display = 'none';
            activeCalendarInput = null;
        }, 300);
    }

    document.querySelectorAll('input[id="start_date"], input[id="end_date"]').forEach(input => {
        input.addEventListener('click', (e) => {
            e.stopPropagation();
            if (activeCalendarInput === e.target) {
                hideCalendar();
            } else {
                showCalendar(e.target);
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (activeCalendarInput && !calendarElement.contains(e.target) && e.target !== activeCalendarInput) {
            hideCalendar();
        }
    });
});
