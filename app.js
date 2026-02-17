document.addEventListener('DOMContentLoaded', () => {
    // Select DOM elements
    const nameElement = document.getElementById('name');
    const roleElement = document.getElementById('role');
    const bioElement = document.getElementById('bio');
    const skillsList = document.getElementById('skills');
    const projectsContainer = document.getElementById('projects');

    // Function to create project card
    const createProjectCard = (project) => {
        const card = document.createElement('article');
        card.className = 'project-card';

        const title = document.createElement('h3');
        title.textContent = project.title;

        const description = document.createElement('p');
        description.textContent = project.description;

        const link = document.createElement('a');
        link.href = project.link;
        link.target = '_blank';
        link.className = 'project-link';
        link.textContent = 'Zobrazit projekt';
        
        // Accessibility attribute
        link.setAttribute('aria-label', `Zobrazit projekt ${project.title}`);

        card.appendChild(title);
        card.appendChild(description);
        card.appendChild(link);

        return card;
    };

    // Fetch data via API simulation (local file)
    fetch('profile.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Populate Header Info
            nameElement.textContent = data.name;
            if (data.role && roleElement) roleElement.textContent = data.role;
            if (data.bio && bioElement) bioElement.textContent = data.bio;

            // Populate Skills
            if (data.skills && Array.isArray(data.skills)) {
                data.skills.forEach(skill => {
                    const li = document.createElement('li');
                    li.textContent = skill;
                    skillsList.appendChild(li);
                });
            }

            // Populate Projects
            if (data.projects && Array.isArray(data.projects)) {
                data.projects.forEach(project => {
                    const projectCard = createProjectCard(project);
                    projectsContainer.appendChild(projectCard);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching profile data:', error);
            
            // Show error message on UI
            nameElement.textContent = 'Chyba načítání dat';
            nameElement.style.color = '#ef4444'; // Red error color
            
            const errorMsg = document.createElement('p');
            errorMsg.textContent = 'Nepodařilo se načíst profilová data. Zkontrolujte prosím konzoli nebo soubor profile.json.';
            errorMsg.style.textAlign = 'center';
            errorMsg.style.color = '#94a3b8';
            
            // Insert error message after header
            document.querySelector('.profile-header').after(errorMsg);
        });
});
