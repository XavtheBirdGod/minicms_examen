## Editorial Lock
- Bestand: PostRepository.php, PostController.php, post-edit.php
- Methode: getPostWithLock, updateLock en releaseLock toevoegen, ook veranderingen in edit() en update()
- Verantwoordelijkheid van deze laag: zorgt ervoor dat geen twee gebruikers op dezelfde post kunnen bewerken (voorkomt edit collision) en heeft de info hierover weer op de view.
- Wat zou breken bij verplaatsing naar repository: Voorbeeld: de 60 seconden check, dan wordt de code minder flexibel. 
De repository kan ook geen redirects of flash meldingen geven.
## Revisies
- Bestand: createRevision(), showRevision(), restore() en update()
- Methode: Veranderingen in de update en het maken van createRevision, ook een showRevision en een restore zijn toegevoegd. de post-edit is aangepast om de laatste drie te tonen en herstellen
- Afhandeling maximum revisies: in de methode createRevision word na het aanmaken meteen een delete uitgevoerd, 
deze heeft een subquery met een limit van 3 zodat de database niet wordt overbelast.

