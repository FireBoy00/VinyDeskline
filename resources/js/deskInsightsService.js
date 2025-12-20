/**
 * Desk Insights Service
 * Generates intelligent insights, briefings, and feedback based on desk usage metrics
 */

export class DeskInsightsService {
    constructor(metrics) {
        this.metrics = metrics;
        this.todayMetrics = this.getTodayMetrics();
        this.analysis = this.analyzeMetrics();
    }

    /**
     * Get today's metrics only (strict - no fallback)
     */
    getTodayMetrics() {
        if (this.metrics.length === 0) return [];

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        return this.metrics.filter((metric) => {
            const metricDate = new Date(metric.recorded_at);
            metricDate.setHours(0, 0, 0, 0);
            return metricDate.getTime() === today.getTime();
        });
    }

    /**
     * Check if we have data for today
     */
    hasTodayData() {
        return this.todayMetrics.length > 0;
    }

    /**
     * Get metrics for the last 7 days
     */
    getLast7DaysMetrics() {
        if (this.metrics.length === 0) return [];

        const today = new Date();
        today.setHours(23, 59, 59, 999);
        const sevenDaysAgo = new Date(today);
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 6);
        sevenDaysAgo.setHours(0, 0, 0, 0);

        return this.metrics.filter((metric) => {
            const metricDate = new Date(metric.recorded_at);
            return metricDate >= sevenDaysAgo && metricDate <= today;
        });
    }

    /**
     * Get metrics for the last 30 days
     */
    getLast30DaysMetrics() {
        if (this.metrics.length === 0) return [];

        const today = new Date();
        today.setHours(23, 59, 59, 999);
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 29);
        thirtyDaysAgo.setHours(0, 0, 0, 0);

        return this.metrics.filter((metric) => {
            const metricDate = new Date(metric.recorded_at);
            return metricDate >= thirtyDaysAgo && metricDate <= today;
        });
    }

    /**
     * Analyze metrics and calculate key statistics
     */
    analyzeMetrics() {
        const today = this.todayMetrics;
        const allMetrics = this.metrics;

        // Sort metrics by time
        const sortedToday = [...today].sort(
            (a, b) =>
                new Date(a.recorded_at).getTime() -
                new Date(b.recorded_at).getTime()
        );

        // Calculate sitting/standing durations
        let totalSittingMinutes = 0;
        let totalStandingMinutes = 0;
        let longestSittingStretch = 0;
        let longestStandingStretch = 0;
        let currentStretch = 0;
        let lastPosition = null;
        let positionChanges = 0;

        // Time of day analysis
        const morningStanding = []; // 6am-12pm
        const afternoonStanding = []; // 12pm-6pm
        const eveningStanding = []; // 6pm-12am

        sortedToday.forEach((metric, index) => {
            const nextMetric = sortedToday[index + 1];
            if (nextMetric) {
                const timeDiff =
                    new Date(nextMetric.recorded_at) -
                    new Date(metric.recorded_at);
                const minutes = timeDiff / (1000 * 60);

                // Only count if the gap is reasonable (e.g., < 15 minutes)
                if (minutes < 15) {
                    if (metric.is_sitting) {
                        totalSittingMinutes += minutes;
                        currentStretch =
                            lastPosition === "sitting"
                                ? currentStretch + minutes
                                : minutes;
                        longestSittingStretch = Math.max(
                            longestSittingStretch,
                            currentStretch
                        );
                    } else {
                        totalStandingMinutes += minutes;
                        currentStretch =
                            lastPosition === "standing"
                                ? currentStretch + minutes
                                : minutes;
                        longestStandingStretch = Math.max(
                            longestStandingStretch,
                            currentStretch
                        );

                        // Track time of day
                        const hour = new Date(metric.recorded_at).getHours();
                        if (hour >= 6 && hour < 12)
                            morningStanding.push(metric);
                        else if (hour >= 12 && hour < 18)
                            afternoonStanding.push(metric);
                        else if (hour >= 18) eveningStanding.push(metric);
                    }

                    // Track position changes
                    if (
                        lastPosition &&
                        lastPosition !==
                            (metric.is_sitting ? "sitting" : "standing")
                    ) {
                        positionChanges++;
                    }
                    lastPosition = metric.is_sitting ? "sitting" : "standing";
                } else {
                    // Gap too large, reset stretch
                    currentStretch = 0;
                    lastPosition = null;
                }
            }
        });

        // Calculate weekly averages
        const last7Days = this.getLast7DaysStats();
        const avgDailySitting =
            last7Days.reduce((sum, day) => sum + day.sitting, 0) / 7;
        const avgDailyStanding =
            last7Days.reduce((sum, day) => sum + day.standing, 0) / 7;

        return {
            today: {
                totalSittingMinutes: Math.round(totalSittingMinutes),
                totalStandingMinutes: Math.round(totalStandingMinutes),
                longestSittingStretch: Math.round(longestSittingStretch),
                longestStandingStretch: Math.round(longestStandingStretch),
                positionChanges,
                morningStanding: morningStanding.length,
                afternoonStanding: afternoonStanding.length,
                eveningStanding: eveningStanding.length,
                totalMinutes: Math.round(
                    totalSittingMinutes + totalStandingMinutes
                ),
            },
            weekly: {
                avgDailySitting: Math.round(avgDailySitting),
                avgDailyStanding: Math.round(avgDailyStanding),
            },
        };
    }

    /**
     * Get last 7 days statistics
     */
    getLast7DaysStats() {
        const stats = [];
        const today = new Date();

        for (let i = 6; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            date.setHours(0, 0, 0, 0);

            const dayMetrics = this.metrics.filter((metric) => {
                const metricDate = new Date(metric.recorded_at);
                metricDate.setHours(0, 0, 0, 0);
                return metricDate.getTime() === date.getTime();
            });

            let sitting = 0;
            let standing = 0;

            dayMetrics.forEach((metric, index) => {
                const nextMetric = dayMetrics[index + 1];
                if (nextMetric) {
                    const timeDiff =
                        new Date(nextMetric.recorded_at) -
                        new Date(metric.recorded_at);
                    const minutes = timeDiff / (1000 * 60);

                    if (minutes < 15) {
                        if (metric.is_sitting) {
                            sitting += minutes;
                        } else {
                            standing += minutes;
                        }
                    }
                }
            });

            stats.push({ date, sitting, standing });
        }

        return stats;
    }

    /**
     * Generate Daily Briefing message (max 200-210 chars)
     * Only shows data for today
     */
    generateDailyBriefing() {
        // No data for today specifically
        if (!this.hasTodayData()) {
            if (this.metrics.length === 0) {
                return "Welcome! Start using your desk and we'll provide insights about your posture habits.";
            }
            return "No desk activity recorded today yet. We'll update your insights as you use your desk.";
        }

        const parts = [];
        const { today } = this.analysis;

        // Greeting based on time
        const hour = new Date().getHours();
        let greeting = "Good morning";
        if (hour >= 12 && hour < 18) greeting = "Good afternoon";
        else if (hour >= 18) greeting = "Good evening";

        parts.push(greeting);

        // Main observation
        const sittingPercent =
            (today.totalSittingMinutes / today.totalMinutes) * 100;

        if (sittingPercent > 80) {
            parts.push(
                "You've been sitting quite a bit today. Consider taking standing breaks to improve circulation"
            );
        } else if (sittingPercent < 30) {
            parts.push(
                "Great job staying active! You've maintained good standing time today"
            );
        } else if (today.positionChanges >= 5) {
            parts.push(
                "Nice work alternating positions! You changed between sitting and standing " +
                    today.positionChanges +
                    " times"
            );
        } else if (today.longestSittingStretch > 120) {
            parts.push(
                "You sat for over 2 hours straight. Try standing breaks every hour for better posture"
            );
        } else {
            parts.push(
                "You're maintaining a balanced sitting-standing routine today"
            );
        }

        // Join and trim to 210 chars
        let message = parts.join(". ") + ".";
        if (message.length > 210) {
            message = message.substring(0, 207) + "...";
        }

        return message;
    }

    /**
     * Generate Feedback observations and suggestions
     * Observations: Today only
     * Suggestions: Based on different time ranges (today, this week, this month)
     */
    generateFeedback() {
        const observations = [];
        const suggestions = [];

        // Handle no data at all
        if (this.metrics.length === 0) {
            return {
                observations: ["No desk usage data recorded yet."],
                suggestions: [
                    "Start using your desk to track your posture patterns and receive personalized recommendations.",
                ],
            };
        }

        // Handle no data for today
        if (!this.hasTodayData()) {
            return {
                observations: ["No desk activity recorded today yet."],
                suggestions: this.generateSuggestionsFromHistory(),
            };
        }

        const { today } = this.analysis;

        // OBSERVATIONS - TODAY ONLY
        if (today.totalSittingMinutes > 0) {
            observations.push(
                `You've sat for ${this.formatHours(
                    today.totalSittingMinutes
                )} today.`
            );
        }

        if (today.totalStandingMinutes > 0) {
            observations.push(
                `You've stood for ${this.formatHours(
                    today.totalStandingMinutes
                )} today.`
            );
        }

        if (today.positionChanges > 0) {
            observations.push(
                `You changed positions ${today.positionChanges} time${
                    today.positionChanges > 1 ? "s" : ""
                } today.`
            );
        }

        if (today.longestSittingStretch >= 60) {
            observations.push(
                `Your longest sitting stretch today was ${this.formatHours(
                    today.longestSittingStretch
                )}.`
            );
        }

        // Time of day patterns - today
        if (
            today.morningStanding > today.afternoonStanding &&
            today.morningStanding > today.eveningStanding
        ) {
            observations.push("You were most active in the morning today.");
        } else if (
            today.afternoonStanding > today.morningStanding &&
            today.afternoonStanding > today.eveningStanding
        ) {
            observations.push("You were most active in the afternoon today.");
        } else if (
            today.eveningStanding > 0 &&
            today.eveningStanding >= today.afternoonStanding
        ) {
            observations.push("You were most active in the evening today.");
        }

        // If very little data today
        if (observations.length === 0) {
            observations.push("Limited desk activity recorded today so far.");
        }

        // SUGGESTIONS - Based on different time ranges
        suggestions.push(...this.generateSuggestionsFromHistory());

        return {
            observations: observations.slice(0, 5),
            suggestions: suggestions.slice(0, 4),
        };
    }

    /**
     * Generate suggestions based on historical data with time range context
     */
    generateSuggestionsFromHistory() {
        const suggestions = [];
        const { today, weekly } = this.analysis;
        const last7Days = this.getLast7DaysMetrics();
        const last30Days = this.getLast30DaysMetrics();

        // Calculate weekly stats
        let weekTotalSitting = 0;
        let weekTotalStanding = 0;
        const weekStats = this.getLast7DaysStats();
        weekStats.forEach((day) => {
            weekTotalSitting += day.sitting;
            weekTotalStanding += day.standing;
        });

        // Calculate monthly average
        let monthAvgSitting = 0;
        let monthAvgStanding = 0;
        if (last30Days.length > 0) {
            const monthDays = new Set();
            last30Days.forEach((metric) => {
                const date = new Date(metric.recorded_at);
                monthDays.add(date.toDateString());
            });
            const daysWithData = monthDays.size;
            if (daysWithData > 0) {
                monthAvgSitting =
                    this.calculateTotalTime(last30Days, true) / daysWithData;
                monthAvgStanding =
                    this.calculateTotalTime(last30Days, false) / daysWithData;
            }
        }

        // TODAY-based suggestions (if we have today's data)
        if (this.hasTodayData() && today.totalMinutes > 0) {
            const sittingPercent =
                (today.totalSittingMinutes / today.totalMinutes) * 100;

            if (sittingPercent > 75) {
                suggestions.push(
                    "<strong>Today:</strong> You've been sitting over 75% of the time. Try standing for 5-10 minutes every hour to improve circulation."
                );
            }

            if (today.longestSittingStretch > 120) {
                suggestions.push(
                    `<strong>Today:</strong> Your longest sitting stretch was ${this.formatHours(
                        today.longestSittingStretch
                    )}. Aim to stand up every 60-90 minutes to reduce back strain.`
                );
            }

            if (today.positionChanges < 3 && today.totalMinutes > 180) {
                suggestions.push(
                    "<strong>Today:</strong> You've changed positions less than 3 times. Try alternating between sitting and standing every 30-45 minutes."
                );
            }
        }

        // THIS WEEK-based suggestions
        if (last7Days.length > 0 && weekTotalSitting + weekTotalStanding > 0) {
            const weekSittingPercent =
                (weekTotalSitting / (weekTotalSitting + weekTotalStanding)) *
                100;

            if (weekSittingPercent > 80) {
                suggestions.push(
                    "<strong>This week:</strong> You've been sitting over 80% of the time. Ergonomists recommend at least 2 hours of standing per workday."
                );
            } else if (weekSittingPercent < 40) {
                suggestions.push(
                    "<strong>This week:</strong> Excellent balance! You're maintaining a healthy sitting-to-standing ratio. Keep it up!"
                );
            } else {
                suggestions.push(
                    "<strong>This week:</strong> You have a good balance between sitting and standing. Keep it up!"
                );
            }

            if (
                this.hasTodayData() &&
                weekly.avgDailySitting > 0 &&
                today.totalSittingMinutes > weekly.avgDailySitting * 1.4
            ) {
                suggestions.push(
                    `<strong>This week:</strong> Today you're sitting ${Math.round(
                        (today.totalSittingMinutes / weekly.avgDailySitting -
                            1) *
                            100
                    )}% more than your weekly average. Consider taking more standing breaks.`
                );
            }
        }

        // THIS MONTH-based suggestions
        if (last30Days.length > 0 && monthAvgSitting > 0) {
            if (monthAvgSitting > 300) {
                // Over 5 hours average per day
                suggestions.push(
                    "<strong>This month:</strong> Your average sitting time is over 5 hours per day. Try incorporating the 20-20-20 rule: every 20 minutes, stand for 20 seconds."
                );
            }

            if (monthAvgStanding < 60) {
                // Less than 1 hour average per day
                suggestions.push(
                    "<strong>This month:</strong> Your standing time averages less than an hour daily. Gradually increase to 2-4 hours for optimal health benefits."
                );
            }
        }

        // GENERAL suggestions
        suggestions.push(
            "<strong>General tip:</strong> Keep your screen at eye level and maintain good posture whether sitting or standing."
        );
        suggestions.push(
            "<strong>General tip:</strong> Use an anti-fatigue mat when standing to reduce foot and leg discomfort."
        );

        return suggestions;
    }

    /**
     * Calculate total time (sitting or standing) from metrics
     */
    calculateTotalTime(metrics, isSitting) {
        const sorted = [...metrics].sort(
            (a, b) => new Date(a.recorded_at) - new Date(b.recorded_at)
        );

        let total = 0;
        sorted.forEach((metric, index) => {
            if (metric.is_sitting === isSitting) {
                const nextMetric = sorted[index + 1];
                if (nextMetric) {
                    const timeDiff =
                        new Date(nextMetric.recorded_at) -
                        new Date(metric.recorded_at);
                    const minutes = timeDiff / (1000 * 60); // Convert to minutes

                    if (minutes < 15) {
                        total += minutes;
                    }
                }
            }
        });

        return Math.round(total);
    }

    /**
     * Format minutes into hours/minutes readable format
     */
    formatHours(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        if (hours === 0) {
            return `${mins} minutes`;
        } else if (mins === 0) {
            return `${hours} hour${hours > 1 ? "s" : ""}`;
        } else {
            return `${hours} hour${hours > 1 ? "s" : ""} ${mins} minutes`;
        }
    }
}
