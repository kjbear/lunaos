# Executive Board Feature - AGENT ORCHESTRATION

**Status:** ✅ Implementation Complete  
**Date:** March 2, 2026  
**Author:** Chen (DevOps Agent)

---

## Overview

The Executive Board feature enables strategic decision-making through AI-powered debate sessions. Five executive personas (COO, CFO, CTO, CMO, CPO) engage in multi-round debates, responding to each other's points before a consolidated decision is generated.

---

## Architecture

### Components

1. **Persona Classes** (`app/Agents/Personas/`)
   - `ExecutivePersona.php` - Base class defining persona structure
   - `COOPersona.php` - Operations focus (Gwynne Shotwell inspired)
   - `CFOPersona.php` - Financial focus (Warren Buffett inspired)
   - `CTOPersona.php` - Technology focus (Werner Vogels inspired)
   - `CMOPersona.php` - Marketing focus (Bozoma Saint John inspired)
   - `CPOPersona.php` - Product focus (Fidji Simo inspired)

2. **Services** (`app/Services/`)
   - `BoardDebateOrchestrator.php` - Manages debate rounds and persona responses
   - `BoardDecisionConsolidator.php` - Analyzes responses and generates unified decision

3. **Configuration** (`config/executive-board.php`)
   - Persona definitions
   - Model assignments (GLM-5 default)
   - Round limits (2 rounds default)
   - Timeout settings (120s per persona)

4. **Database** (`database/migrations/`)
   - `board_sessions` - Stores debate sessions and final decisions
   - `board_responses` - Stores individual persona responses with round tracking

---

## Debate Flow

```
1. User submits strategic question
   └─> Optional: Additional context

2. Orchestrator initializes
   └─> Loads 5 personas from config
   └─> Creates BoardSession record

3. Round 1 (Initial Perspectives)
   ├── COO responds (operations viewpoint)
   ├── CFO responds (financial viewpoint)
   ├── CTO responds (technical viewpoint)
   ├── CMO responds (market viewpoint)
   └── CPO responds (product viewpoint)

4. Round 2 (Response & Debate)
   ├── Each persona sees Round 1 responses
   ├── Can agree, disagree, or build on ideas
   └── More nuanced positions emerge

5. Decision Consolidation
   ├── All responses analyzed
   ├── Key themes extracted
   ├── Confidence score calculated
   ├── Dissenting opinions noted
   └── Unified recommendation generated

6. Results Display
   ├── Full transcript shown
   ├── Final decision displayed
   └── Confidence score & risks/benefits shown
```

---

## Usage

### Via UI (Livewire Component)

```php
// Component: App\Livewire\Board\ExecutiveBoard
// View: resources/views/livewire/board/executive-board.blade.php

1. Navigate to /board/executive
2. Enter strategic question
3. Add optional context
4. Click "Convene Board"
5. Wait for debate to complete (~2-5 minutes)
6. Review transcript and decision
```

### Via Code

```php
use App\Services\BoardDebateOrchestrator;

$orchestrator = app(BoardDebateOrchestrator::class);

$result = $orchestrator->runDebate(
    "Should we prioritize LunaOS development or the Status Page Aggregator?",
    "Budget: $100k, Timeline: Q1-Q2, Team: 5 engineers"
);

// Result structure:
// [
//   'session_id' => 'uuid',
//   'transcript' => [...], // All persona responses
//   'decision' => [
//     'recommendation' => string,
//     'reasoning' => string,
//     'risks_benefits' => string,
//     'confidence_score' => 0.0-1.0,
//     'dissenting_opinions' => array,
//     'key_themes' => array,
//     'action_items' => array,
//   ]
// ]
```

---

## Configuration

### config/executive-board.php

```php
return [
    // Personas (order = response order)
    'personas' => [
        ['class' => COOPersona::class, 'model' => 'glm-5'],
        ['class' => CFOPersona::class, 'model' => 'glm-5'],
        ['class' => CTOPersona::class, 'model' => 'glm-5'],
        ['class' => CMOPersona::class, 'model' => 'glm-5'],
        ['class' => CPOPersona::class, 'model' => 'glm-5'],
    ],
    
    // Model for all personas
    'model' => 'glm-5',
    
    // Number of debate rounds
    'rounds' => 2,
    
    // Timeout per persona (seconds)
    'timeout_seconds' => 120,
    
    // Response token limit
    'max_response_tokens' => 600,
    
    // Creativity/temperature
    'temperature' => 0.7,
];
```

---

## API Integration

### Current: OpenRouter Direct API

The current implementation uses OpenRouter API directly for speed and simplicity:

```php
// BoardDebateOrchestrator::executePersonaTask()
$response = Http::post('https://openrouter.ai/api/v1/chat/completions', [
    'model' => 'z-ai/glm-5',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt],
    ],
]);
```

### Future: OpenClaw sessions_spawn() Integration

**TODO:** Replace direct API calls with OpenClaw's agent orchestration:

```php
// In BoardDebateOrchestrator::executePersonaTask()
$result = sessions_spawn(
    task: $this->buildPersonaTask($systemPrompt, $userPrompt, $model),
    model: 'glm-5',
    timeoutSeconds: $this->personaTimeout,
    mode: 'run',
);

// Wait for completion and extract response
```

**Benefits:**
- Centralized session management
- Better error handling and retries
- Consistent with other OpenClaw features
- Support for agent memory and context

**Requirements:**
- OpenClaw bridge to PHP/Laravel
- WebSocket or polling for session results
- Session persistence layer

---

## Testing

### Run Tests

```bash
cd /Users/kobear/.openclaw/workspace/lunaos
php artisan test --filter=ExecutiveBoardDebateTest
```

### Test Coverage

- ✅ Persona instantiation
- ✅ Prompt building (with/without context)
- ✅ Multi-round debate structure
- ✅ Database session creation
- ✅ Decision consolidation
- ✅ Configuration loading

### Manual Test

```bash
# In Laravel Tinker
php artisan tinker

>>> use App\Services\BoardDebateOrchestrator;
>>> $orchestrator = new BoardDebateOrchestrator();
>>> $result = $orchestrator->runDebate(
    "Should we open-source LunaOS?",
    "Pros: community, adoption. Cons: support burden, competition."
);
>>> print_r($result['decision']['recommendation']);
```

---

## Database Schema

### board_sessions

```sql
id                 VARCHAR(36) PRIMARY KEY
question           TEXT
context            TEXT NULL
status             VARCHAR (pending|debating|decided)
rounds_planned     INT DEFAULT 2
final_decision     TEXT NULL
risks_benefits     TEXT NULL
confidence_score   DECIMAL(3,2) DEFAULT 0.50
dissenting_opinions JSON NULL
key_themes         JSON NULL
decided_at         TIMESTAMP NULL
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

### board_responses

```sql
id                 BIGINT AUTO_INCREMENT
session_id         VARCHAR(36) FK
member_name        VARCHAR
member_role        VARCHAR (COO, CFO, etc.)
response           TEXT
model_used         VARCHAR NULL
response_order     INT DEFAULT 0
round              INT DEFAULT 1
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

---

## Personas Detail

### Gwynne (COO)
- **Inspiration:** Gwynne Shotwell
- **Expertise:** Operations, execution, scalability
- **Debate Style:** Pragmatic, realistic
- **Focus:** "How do we actually make this happen?"

### Warren (CFO)
- **Inspiration:** Warren Buffett
- **Expertise:** Finance, ROI, capital allocation
- **Debate Style:** Analytical, detail-oriented
- **Focus:** "What's the return on investment?"

### Werner (CTO)
- **Inspiration:** Werner Vogels
- **Expertise:** Architecture, scalability, security
- **Debate Style:** Analytical, systems-thinking
- **Focus:** "Everything fails—plan for it"

### Bozoma (CMO)
- **Inspiration:** Bozoma Saint John
- **Expertise:** Brand, marketing, cultural trends
- **Debate Style:** Assertive, bold
- **Focus:** "How do we stand out?"

### Fidji (CPO)
- **Inspiration:** Fidji Simo
- **Expertise:** Product, users, data-driven decisions
- **Debate Style:** Collaborative, user-focused
- **Focus:** "What problem are we solving?"

---

## Performance

### Timing (Expected)

- **Per Persona:** 10-30 seconds (OpenRouter API)
- **Per Round:** 1-3 minutes (5 personas, sequential)
- **Full Debate (2 rounds):** 2-6 minutes
- **Decision Consolidation:** 10-20 seconds

### Optimization Opportunities

- Parallel persona responses (concurrent API calls)
- Response caching for similar questions
- Shorter max_tokens for faster responses
- WebSockets for real-time transcript updates

---

## Error Handling

### Fallback Strategy

1. **API Failure:** Create placeholder responses with error message
2. **Timeout:** Mark response as timed out, continue with next persona
3. **Invalid JSON:** Use raw response in decision consolidation
4. **No Decision:** Return transcript with manual decision prompt

### Logging

```php
BoardDebateOrchestrator: Starting debate session
BoardDebateOrchestrator: Starting round 1
BoardDebateOrchestrator: Got response from Gwynne (1200 chars)
BoardDebateOrchestrator: Debate session complete (8 responses, confidence: 0.78)
```

---

## Future Enhancements

### Phase 2 (OpenClaw Integration)

- [ ] Replace direct API calls with `sessions_spawn()`
- [ ] Add session persistence and recovery
- [ ] Implement WebSocket for real-time updates
- [ ] Support for agent memory across rounds

### Phase 3 (Advanced Features)

- [ ] CEO persona to moderate and guide debate
- [ ] Custom personas per question type
- [ ] Historical debate search and comparison
- [ ] Export decisions to projects/tasks
- [ ] Confidence scoring based on consensus level

### Phase 4 (AI Improvements)

- [ ] Fine-tuned models for specific personas
- [ ] Better dissent detection and handling
- [ ] Automated action item extraction
- [ ] Integration with project tracking tools

---

## Files Created/Modified

### New Files
- `app/Agents/Personas/ExecutivePersona.php`
- `app/Agents/Personas/COOPersona.php`
- `app/Agents/Personas/CFOPersona.php`
- `app/Agents/Personas/CTOPersona.php`
- `app/Agents/Personas/CMOPersona.php`
- `app/Agents/Personas/CPOPersona.php`
- `app/Services/BoardDebateOrchestrator.php`
- `app/Services/BoardDecisionConsolidator.php`
- `config/executive-board.php`
- `database/migrations/2026_03_02_120000_update_board_tables_for_orchestration.php`
- `tests/Feature/ExecutiveBoardDebateTest.php`
- `docs/executive-board-agent-orchestration.md` (this file)

### Modified Files
- `app/Livewire/Board/ExecutiveBoard.php`
- `app/Models/BoardSession.php`
- `app/Models/BoardResponse.php`

---

## Commit Checklist

- [x] Persona classes created
- [x] Base class with prompt building
- [x] Debate orchestrator implemented
- [x] Decision consolidator implemented
- [x] Configuration file created
- [x] Database migration created
- [x] Models updated for new fields
- [x] Livewire component updated
- [x] Tests written
- [x] Documentation complete
- [ ] Run migration: `php artisan migrate`
- [ ] Run tests: `php artisan test`
- [ ] Manual UI testing
- [ ] Commit all changes

---

## Contact

Implementer: Chen (DevOps Engineer)  
Questions: Open a GitHub issue or check `/board/executive` UI  
Next Steps: OpenClaw `sessions_spawn()` integration (Phase 2)
