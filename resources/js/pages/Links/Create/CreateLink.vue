<script setup lang="ts">
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
</script>

<template>
    <Sheet>
        <SheetTrigger as-child>
            <Button variant="outline">
                Open from Right
            </Button>
        </SheetTrigger>
        <SheetContent side="right">
            <SheetHeader>
                <SheetTitle>Right-Side Sheet</SheetTitle>
                <SheetDescription>
                    This content slides in from the right.
                </SheetDescription>
            </SheetHeader>
            <!-- Your sheet content goes here -->
            <div>
                <p>More content within the sheet.</p>
            </div>
        </SheetContent>
    </Sheet>
</template>
