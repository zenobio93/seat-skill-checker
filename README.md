# SeAT Skill Checker

A comprehensive skill management plugin for [SeAT](https://github.com/eveseat/seat) that allows you to create skill plans and check character skills against requirements for individuals, squads, and corporations.

[![Latest Stable Version](https://img.shields.io/packagist/v/zenobio93/seat-skill-checker?style=for-the-badge)](https://packagist.org/packages/zenobio93/seat-skill-checker)
[![License](https://img.shields.io/badge/license-GPL--3.0-blue.svg?style=for-the-badge)](https://raw.githubusercontent.com/zenobio93/seat-skill-checker/main/LICENSE)
[![SeAT](https://img.shields.io/badge/SeAT-5.0.x-blueviolet?style=for-the-badge)](https://github.com/eveseat/seat)

## Features

- **Skill Plan Management**: Create and manage custom skill plans with required skill levels
- **Individual Character Checking**: Check any character's skills against skill plans
- **User-based Checking**: Check all characters belonging to a user (grouped by main character)
- **Squad Skill Checking**: Analyze skill compliance across entire squads
- **Corporation Analysis**: Check skill requirements for all corporation members
- **Character Sheet Integration**: View skill check results directly in character sheets
- **Real-time Results**: Dynamic skill checking with detailed completion percentages
- **Permission System**: Granular permissions for managing skill plans and checking skills

## Installation

### Requirements

- SeAT 5.0.x
- PHP 8.2+
- EVE Online ESI data (automatically handled by SeAT)

### Quick Installation

1. Install the package via Composer:
```bash
composer require zenobio93/seat-skill-checker
```

2. Run migrations:
```bash
php artisan migrate
```

3. Clear caches:
```bash
php artisan config:cache
php artisan route:cache
php artisan seat:cache:clear
```

4. Grant permissions to users through SeAT's permission system

## Usage

### Creating Skill Plans

1. Navigate to **Skill Checker > Skill Plans** in the SeAT sidebar
2. Click **Create New Skill Plan**
3. Enter a name and optional description
4. Add skills by clicking **Add Skill** and selecting from the modal
5. Set required levels (I-V) for each skill
6. Save the skill plan

### Checking Skills

#### Individual User Checking
1. Go to **Skill Checker > Skill Checker**
2. Select a user from the dropdown
3. Choose a skill plan to check against
4. Click **Check** to see results grouped by main character

#### Squad Analysis
1. Select a squad from the dropdown
2. Choose a skill plan
3. Results show all characters belonging to squad members, grouped by main character
4. View summary statistics including completion percentages

#### Corporation Analysis
1. Select a corporation from the dropdown
2. Choose a skill plan
3. Analyze all corporation members' skills
4. Results are grouped by main character for better organization

#### Character Sheet Integration
- Navigate to any character sheet
- Click the **Skillcheck** tab
- View all skill plans and their completion status for that character
- Expand individual skill plans to see detailed requirements

### Permissions

The plugin uses SeAT's permission system with the following permissions:

- `skillchecker.manage_skill_plans`: Create, edit, and delete skill plans
- `skillchecker.check_skills`: Access the skill checker interface
- `character.skillchecker_skillcheck`: View skill checks in character sheets

## Database Structure

The plugin creates two main tables:

- `skill_plans`: Stores skill plan information (name, description, creator)
- `skill_plan_requirements`: Stores individual skill requirements with levels

## API Integration

The plugin integrates with SeAT's existing EVE Online data:

- Uses `character_skills` table for current character skill levels
- Leverages `invTypes` for skill information and names
- Connects to `invGroups` for skill categorization
- Utilizes SeAT's user and character management system

## Development

### Package Structure
```
src/
├── Config/                 # Configuration files
├── Http/Controllers/       # Web controllers
├── Models/                # Eloquent models
├── database/migrations/   # Database migrations
├── lang/                  # Translation files
└── resources/views/       # Blade templates
```

### Key Models

- `SkillPlan`: Manages skill plan data and character skill checking logic
- `SkillPlanRequirement`: Handles individual skill requirements within plans

### Key Controllers

- `SkillPlanController`: CRUD operations for skill plans
- `SkillCheckerController`: Skill checking functionality for users, squads, and corporations
- `CharacterController`: Character sheet integration

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Support

If you encounter any issues or have questions:

- Create an issue on [GitHub](https://github.com/zenobio93/seat-skill-checker/issues)

## License

This plugin is licensed under the [GNU General Public License v3.0](LICENSE).

## Changelog

### Version 1.0.0
- Initial release
- Skill plan management
- Character, squad, and corporation skill checking
- Character sheet integration
- Permission system integration
- Real-time skill analysis with completion percentages

---

**Happy skill checking!** o7