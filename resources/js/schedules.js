document.addEventListener("DOMContentLoaded", () =>
{
    const saveUniformBtn= document.getElementById("saveUniformBtn");
    const saveCleaningBtn= document.getElementById("saveCleaningBtn");

    const uniformList =document.getElementById("uniformList");
    const cleaningList =document.getElementById("cleaningList");

    const uniformStart = document.getElementById("uniformStart");
    const cleaningStart = document.getElementById("cleaningStart");

    const uniformEnd = document.getElementById("uniformEnd");
    const cleaningEnd = document.getElementById("cleaningEnd");

    const uniformTitle = document.getElementById("uniform-title");
    const cleaningTitle = document.getElementById("cleaning-title");

    const template = document.getElementById("scheduleTemplate");

    function addUniform() 
    {
        const title = uniformTitle.value.trim();
        const start = uniformStart.value;
        const end = uniformEnd.value;
        
        const clone = template.content.cloneNode(true);

        const label = clone.querySelector(".schedule-label");
        const timeText = clone.querySelector(".schedule-time");
        const deleteBtn = clone.querySelector(".delete-btn");

        label.textContent = title + ":";
        timeText.textContent = `${start} - ${end}`;
        deleteBtn.addEventListener("click", () => 
        {
            deleteBtn.closest(".current-schedule").remove();
        });
        
        uniformList.appendChild(clone)

    }

    function addCleaning() 
    {
        const title = cleaningTitle.value.trim();
        const start = cleaningStart.value;
        const end = cleaningEnd.value;
        
        const clone = template.content.cloneNode(true);

        const label = clone.querySelector(".schedule-label");
        const timeText = clone.querySelector(".schedule-time");
        const deleteBtn = clone.querySelector(".delete-btn");

        label.textContent = title + ":";
        timeText.textContent = `${start} - ${end}`;
        deleteBtn.addEventListener("click", () => 
        {
            deleteBtn.closest(".current-schedule").remove();
        });
        
        cleaningList.appendChild(clone)

    }
    
    saveUniformBtn.addEventListener("click", addUniform);
    saveCleaningBtn.addEventListener("click", addCleaning);

});

